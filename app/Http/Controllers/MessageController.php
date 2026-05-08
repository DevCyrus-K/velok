<?php

namespace App\Http\Controllers;

use App\Mail\MessageMail;
use App\Models\EmailLog;
use App\Models\Message;
use App\Support\TopbarData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class MessageController extends Controller
{
    private const ATTACHMENT_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'text/plain',
        'text/csv',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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
        $message->load('latestEmailLog', 'respondedByUser');

        return view('messages.show', compact('message'));
    }

    public function compose()
    {
        return view('messages.compose');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimetypes:'.implode(',', self::ATTACHMENT_MIMES)],
        ]);

        $recipient = Str::lower(trim($validated['email']));
        $subject = $this->cleanSubject($validated['subject']);
        $body = $this->cleanBody($validated['message'], 'message');
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

        $emailLog = $this->createEmailLog($recipient, $subject);
        $message->update(['email_log_id' => $emailLog->getKey()]);

        try {
            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                trackingToken: $emailLog->tracking_token,
            ));

            $message->update([
                'status' => 'sent',
                'responded_at' => now(),
            ]);

            $this->markEmailLogSent($emailLog);

            return $this->emailSuccessResponse($request, $message);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
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
        ]);

        $recipient = Str::lower(trim($validated['recipient_email']));
        $subject = $this->cleanSubject($validated['subject']);
        $body = $this->cleanBody($validated['response'], 'response');

        $emailLog = $this->createEmailLog($recipient, $subject);

        $message->update([
            'response' => $body,
            'responded_at' => now(),
            'responded_by' => auth()->id(),
            'email_log_id' => $emailLog->getKey(),
        ]);

        try {
            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                body: $body,
                emailSubject: $subject,
                trackingToken: $emailLog->tracking_token,
            ));

            $message->update(['status' => 'responded']);
            $this->markEmailLogSent($emailLog);

            return $this->emailSuccessResponse($request, $message);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
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
        $isReply = $this->isReplyLog($message, $emailLog);
        $body = $isReply ? (string) $message->response : (string) $message->message;

        $emailLog->update([
            'status' => EmailLog::STATUS_SENDING,
            'failed_reason' => null,
            'attempts' => $emailLog->attempts + 1,
        ]);

        try {
            Mail::to($recipient)->send(new MessageMail(
                message: $message,
                body: $body,
                emailSubject: $subject,
                trackingToken: $emailLog->tracking_token,
                attachmentPath: $isReply ? null : $message->attachment_path,
                attachmentOriginalName: $isReply ? null : $message->attachment_original_name,
                attachmentMime: $isReply ? null : $message->attachment_mime,
            ));

            $message->update([
                'status' => $isReply ? 'responded' : 'sent',
                'responded_at' => $isReply ? ($message->responded_at ?: now()) : $message->responded_at,
            ]);

            $this->markEmailLogSent($emailLog);

            return response()->json([
                'success' => true,
                'message' => 'Email sent',
                'delivery' => $this->deliveryPayload($emailLog->fresh()),
            ]);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
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

    private function createEmailLog(string $recipient, string $subject): EmailLog
    {
        return EmailLog::query()->create([
            'emailable_type' => null,
            'emailable_id' => null,
            'recipient_email' => Str::limit($recipient, 190, ''),
            'subject' => Str::limit($subject, 190, ''),
            'status' => EmailLog::STATUS_SENDING,
            'tracking_token' => (string) Str::uuid(),
            'attempts' => 1,
        ]);
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
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $path = $file->storeAs('message-attachments', (string) Str::uuid().'.'.$extension, 'local');

        return [
            'attachment_path' => $path,
            'attachment_original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            'attachment_mime' => Str::limit((string) $file->getMimeType(), 100, ''),
        ];
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
        ];
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
