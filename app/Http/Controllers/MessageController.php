<?php

namespace App\Http\Controllers;

use App\Mail\MessageMail;
use App\Models\EmailLog;
use App\Models\Message;
use App\Services\MailConfigService;
use App\Services\StorageService;
use App\Support\MailSender;
use App\Support\NotificationLogger;
use App\Support\TopbarData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class MessageController extends Controller
{
    private const ATTACHMENT_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public function index()
    {
        $messages = Message::query()
            ->with('latestEmailLog')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $message->markAsRead();
        app(NotificationLogger::class)->markReadFor($message);
        $message->load('latestEmailLog', 'respondedByUser');

        return view('messages.show', [
            'message' => $message,
            'messageSenders' => app(MailSender::class)->messageOptions(),
        ]);
    }

    public function compose()
    {
        return view('messages.compose', [
            'messageSenders' => app(MailSender::class)->messageOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'sender_role' => ['nullable', Rule::in(MailSender::MESSAGE_ROLES)],
            'attachment' => ['nullable', 'file', 'max:51200', 'mimetypes:'.implode(',', self::ATTACHMENT_MIMES)],
        ]);

        $recipient = Str::lower(trim($validated['email']));
        $subject = $this->cleanSubject($validated['subject']);
        $body = $this->cleanBody($validated['message'], 'message');
        $senderRole = app(MailSender::class)->validMessageRole($validated['sender_role'] ?? null);
        $attachment = $this->storeAttachment($request);

        $message = Message::query()->create([
            'name' => $this->recipientName($recipient),
            'email' => $recipient,
            'phone' => null,
            'subject' => $subject,
            'message' => $body,
            'status' => 'unread',
            'response' => null,
            'responded_at' => null,
            'responded_by' => null,
            'origin_page' => 'compose',
            ...$attachment,
        ]);

        $emailLog = $this->createEmailLog($recipient, $subject, $senderRole);
        $message->update(['email_log_id' => $emailLog->getKey()]);

        try {
            MailConfigService::apply();

            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                trackingToken: $emailLog->tracking_token,
                senderRole: $senderRole,
            ));

            $message->update([
                'status' => 'sent',
                'responded_at' => now(),
            ]);

            $this->markEmailLogSent($emailLog);
            app(NotificationLogger::class)->messageSent($message, $recipient, $subject);
            $this->recordEmailDelivery(
                'message',
                EmailLog::STATUS_SENT,
                $recipient,
                $subject,
                'Message email sent successfully.'
            );

            return $this->emailSuccessResponse($request, $message);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
            app(NotificationLogger::class)->messageFailed($message, $recipient, $exception->getMessage());
            $this->recordEmailDelivery(
                'message',
                EmailLog::STATUS_FAILED,
                $recipient,
                $subject,
                'Email failed: '.$exception->getMessage()
            );
            $this->logEmailFailure($message, $exception);

            return $this->emailFailureResponse($request, $exception);
        }
    }

    public function respond(Request $request, Message $message): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'response' => ['required', 'string'],
            'sender_role' => ['nullable', Rule::in(MailSender::MESSAGE_ROLES)],
        ]);

        $recipient = Str::lower(trim($validated['recipient_email']));
        $subject = $this->cleanSubject($validated['subject']);
        $body = $this->cleanBody($validated['response'], 'response');
        $senderRole = app(MailSender::class)->validMessageRole($validated['sender_role'] ?? null);

        $emailLog = $this->createEmailLog($recipient, $subject, $senderRole);

        $message->update([
            'response' => $body,
            'responded_at' => now(),
            'responded_by' => auth()->id(),
            'email_log_id' => $emailLog->getKey(),
        ]);

        try {
            MailConfigService::apply();

            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                body: $body,
                emailSubject: $subject,
                trackingToken: $emailLog->tracking_token,
                senderRole: $senderRole,
            ));

            $message->update(['status' => 'responded']);
            $this->markEmailLogSent($emailLog);
            app(NotificationLogger::class)->messageSent($message, $recipient, $subject);
            $this->recordEmailDelivery(
                'message_reply',
                EmailLog::STATUS_SENT,
                $recipient,
                $subject,
                'Message reply email sent successfully.'
            );

            return $this->emailSuccessResponse($request, $message);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
            app(NotificationLogger::class)->messageFailed($message, $recipient, $exception->getMessage());
            $this->recordEmailDelivery(
                'message_reply',
                EmailLog::STATUS_FAILED,
                $recipient,
                $subject,
                'Email failed: '.$exception->getMessage()
            );
            $this->logEmailFailure($message, $exception);

            return $this->emailFailureResponse($request, $exception);
        }
    }

    public function retry(Request $request, Message $message): JsonResponse
    {
        $emailLog = $message->latestEmailLog;

        if (! $emailLog || $emailLog->status !== EmailLog::STATUS_FAILED) {
            return response()->json([
                'success' => false,
                'error' => 'There is no failed email to retry.',
            ], 422);
        }

        $recipient = Str::lower(trim($emailLog->recipient_email ?: $message->email));
        $subject = $this->cleanSubject($emailLog->subject ?: $message->subject);
        $senderRole = app(MailSender::class)->validMessageRole($emailLog->sender_role ?: MailSender::INFO);
        $isReply = $this->isReplyLog($message, $emailLog);
        $body = $isReply ? (string) $message->response : (string) $message->message;
        $sender = app(MailSender::class)->sender($senderRole);

        $emailLogUpdate = [
            'status' => EmailLog::STATUS_SENDING,
            'failed_reason' => null,
            'attempts' => $emailLog->attempts + 1,
        ];

        if (Schema::hasColumn('email_logs', 'sender_role')) {
            $emailLogUpdate['sender_role'] = $senderRole;
            $emailLogUpdate['sender_email'] = $sender['address'];
            $emailLogUpdate['sender_name'] = $sender['name'];
        }

        $emailLog->update($emailLogUpdate);

        try {
            MailConfigService::apply();

            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                body: $body,
                emailSubject: $subject,
                trackingToken: $emailLog->tracking_token,
                attachmentPath: $isReply ? null : $message->attachment_path,
                attachmentOriginalName: $isReply ? null : $message->attachment_original_name,
                attachmentMime: $isReply ? null : $message->attachment_mime,
                senderRole: $senderRole,
            ));

            $message->update([
                'status' => $isReply ? 'responded' : 'sent',
                'responded_at' => $isReply ? ($message->responded_at ?: now()) : $message->responded_at,
            ]);

            $this->markEmailLogSent($emailLog);
            app(NotificationLogger::class)->messageSent($message, $recipient, $subject);
            $this->recordEmailDelivery(
                $isReply ? 'message_reply' : 'message',
                EmailLog::STATUS_SENT,
                $recipient,
                $subject,
                $isReply ? 'Message reply email resent successfully.' : 'Message email resent successfully.'
            );

            return response()->json([
                'success' => true,
                'message' => 'Email sent',
                'delivery' => $this->deliveryPayload($emailLog->fresh()),
            ]);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
            app(NotificationLogger::class)->messageFailed($message, $recipient, $exception->getMessage());
            $this->recordEmailDelivery(
                $isReply ? 'message_reply' : 'message',
                EmailLog::STATUS_FAILED,
                $recipient,
                $subject,
                'Email failed: '.$exception->getMessage()
            );
            $this->logEmailFailure($message, $exception);

            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
                'delivery' => $this->deliveryPayload($emailLog->fresh()),
            ], 500);
        }
    }

    public function destroy(Request $request, Message $message): JsonResponse|RedirectResponse
    {
        $this->deleteStoredAttachment($message);
        $message->delete();
        app(TopbarData::class)->forgetNotifications();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Message deleted',
            ]);
        }

        return redirect()->route('messages.index')
            ->with('toast-success', 'Message deleted');
    }

    public function delete(Request $request, Message $message): JsonResponse|RedirectResponse
    {
        return $this->destroy($request, $message);
    }

    public function storeFromFrontend(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'origin_page' => 'nullable|string|max:255',
        ]);

        $validated['origin_page'] = $validated['origin_page'] ?? 'frontend';

        Message::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will respond shortly.',
        ]);
    }

    private function createEmailLog(string $recipient, string $subject, string $senderRole = MailSender::INFO): EmailLog
    {
        $senderRole = app(MailSender::class)->validMessageRole($senderRole);
        $sender = app(MailSender::class)->sender($senderRole);
        $data = [
            'emailable_type' => null,
            'emailable_id' => null,
            'recipient_email' => Str::limit($recipient, 190, ''),
            'subject' => Str::limit($subject, 190, ''),
            'status' => EmailLog::STATUS_SENDING,
            'tracking_token' => (string) Str::uuid(),
            'attempts' => 1,
        ];

        if (Schema::hasColumn('email_logs', 'sender_role')) {
            $data['sender_role'] = $senderRole;
            $data['sender_email'] = Str::limit($sender['address'], 190, '');
            $data['sender_name'] = Str::limit($sender['name'], 190, '');
        }

        return EmailLog::query()->create($data);
    }

    private function markEmailLogSent(EmailLog $emailLog): void
    {
        $emailLog->update([
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
            'failed_reason' => null,
        ]);
    }

    private function markEmailLogFailed(EmailLog $emailLog, Throwable $exception): void
    {
        $emailLog->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
        ]);
    }

    private function emailSuccessResponse(Request $request, Message $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email sent',
                'message_id' => $message->getKey(),
                'redirect_url' => route('messages.show', $message),
                'delivery' => $this->deliveryPayload($message->latestEmailLog()->first()),
            ], 200)->header('Content-Type', 'application/json');
        }

        return redirect()->route('messages.show', $message)
            ->with('toast-success', 'Email sent successfully');
    }

    private function emailFailureResponse(Request $request, Throwable $exception): JsonResponse|RedirectResponse
    {
        $errorMessage = $exception->getMessage() ?: 'Failed to send email';
        Log::error('Message sending failed', [
            'error' => $errorMessage,
            'exception' => class_basename($exception),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
            ], 500)->header('Content-Type', 'application/json');
        }

        return back()
            ->withInput()
            ->with('toast-error', 'Failed to send: '.$errorMessage);
    }

    private function cleanSubject(string $subject): string
    {
        return Str::limit((string) Str::of(strip_tags($subject))->squish(), 255, '');
    }

    private function cleanBody(string $body, string $field): string
    {
        $cleanBody = trim(strip_tags(str_replace("\0", '', str_replace(["\r\n", "\r"], "\n", $body))));

        if ($cleanBody === '') {
            throw ValidationException::withMessages([
                $field => 'The message body is required.',
            ]);
        }

        return $cleanBody;
    }

    private function storeAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [
                'attachment_path' => null,
                'attachment_original_name' => null,
                'attachment_mime' => null,
            ];
        }

        $file = $request->file('attachment');
        $mime = (string) $file->getMimeType();
        $folder = $mime === 'application/pdf' ? 'pdfs/message-attachments' : 'images/message-attachments';
        $uploaded = app(StorageService::class)->storeUploadedFile($file, $folder);

        $payload = [
            'attachment_path' => $uploaded['key'],
            'attachment_original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            'attachment_mime' => Str::limit($mime, 100, ''),
            'storage_key' => $uploaded['key'],
            'storage_url' => $uploaded['url'],
        ];

        if ($mime === 'application/pdf') {
            $payload['pdf_storage_key'] = $uploaded['key'];
            $payload['pdf_storage_file_id'] = $uploaded['fileId'] ?? null;
            $payload['pdf_storage_url'] = $uploaded['url'];
        } else {
            $payload['image_public_id'] = $uploaded['public_id'] ?? $uploaded['key'];
            $payload['image_url'] = $uploaded['url'];
        }

        return $payload;
    }

    private function deleteStoredAttachment(Message $message): void
    {
        $path = $message->attachment_path;

        if (! is_string($path) || trim($path) === '' || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        if ($message->attachment_mime === 'application/pdf') {
            if (! $message->pdf_storage_file_id) {
                return;
            }

            app(StorageService::class)->deletePDF($message->pdf_storage_file_id, $message->pdf_storage_key ?: $path);

            return;
        }

        app(StorageService::class)->deleteImage($message->image_public_id ?: $path);
    }

    private function recipientName(string $email): string
    {
        $name = (string) Str::of(Str::before($email, '@'))
            ->replace(['.', '_', '-'], ' ')
            ->squish()
            ->title();

        return $name !== '' ? $name : $email;
    }

    private function isReplyLog(Message $message, EmailLog $emailLog): bool
    {
        return filled($message->response)
            && Str::startsWith(Str::lower((string) $emailLog->subject), 're:');
    }

    private function deliveryPayload(?EmailLog $emailLog): ?array
    {
        if (! $emailLog) {
            return null;
        }

        return [
            'status' => $emailLog->status,
            'failed_reason' => $emailLog->failed_reason,
            'attempts' => $emailLog->attempts,
            'sender_role' => $emailLog->sender_role,
            'sender_email' => $emailLog->sender_email,
            'sender_name' => $emailLog->sender_name,
        ];
    }

    private function recordEmailDelivery(string $formType, string $status, string $recipient, string $subject, string $responseMessage): void
    {
        if (! Schema::hasTable('email_delivery_logs')) {
            return;
        }

        $mailer = (string) config('mail.default', '');
        $transport = (string) (config("mail.mailers.{$mailer}.transport") ?: $mailer ?: 'unknown');
        $now = now();
        $data = [
            'form_type' => Str::limit($formType, 190, ''),
            'recipient_email' => Str::limit($recipient, 190, ''),
            'status' => Str::limit($status, 50, ''),
            'direction' => 'client',
            'subject' => Str::limit($subject, 190, ''),
            'transport' => Str::limit($transport, 50, ''),
            'response_message' => Str::limit($responseMessage, 1000, ''),
            'created_at' => $now,
        ];

        if (Schema::hasColumn('email_delivery_logs', 'updated_at')) {
            $data['updated_at'] = $now;
        }

        try {
            DB::table('email_delivery_logs')->insert($data);
        } catch (Throwable $logException) {
            report($logException);
        }
    }

    private function logEmailFailure(Message $message, Throwable $exception): void
    {
        Log::error('Message email failed', [
            'message_id' => $message->getKey(),
            'recipient_email' => $message->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
