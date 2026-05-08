<?php

namespace App\Jobs;

use App\Mail\QuotationMail;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\SentMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendQuotationEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $quotationId,
        private readonly string $recipientEmail,
        private readonly string $subject,
        private readonly string $message,
        private readonly bool $attachPdf = true,
        private readonly ?int $userId = null,
        private readonly ?int $emailLogId = null,
    ) {}

    public function handle(): void
    {
        $quotation = Quotation::query()
            ->with('quoteRequest')
            ->find($this->quotationId);

        if (! $quotation || ! $quotation->quoteRequest) {
            $this->markEmailLogFailed(new RuntimeException('Quotation email failed: quotation record was not found.'));

            return;
        }

        $recipient = Str::lower(trim($this->recipientEmail));
        $transport = $this->mailTransportName();
        $user = $this->userId ? User::query()->find($this->userId) : null;

        try {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Quotation email failed: recipient email address is invalid.');
            }

            $this->ensureDeliverableMailTransport($transport);

            $sentMessage = Mail::to($recipient)->send(new QuotationMail(
                quotation: $quotation,
                messageBody: $this->message,
                subject: $this->subject,
                attachPdf: $this->attachPdf,
                user: $user,
                emailLogId: $this->emailLogId,
            ));

            $messageId = $this->validatedSentMessageId($sentMessage);
            $this->markQuotation($quotation, 'sent');
            $this->markEmailLogSent();
            $this->recordDelivery($quotation, 'sent', $recipient, 'Quotation email accepted by the mail transport. Message ID: '.$messageId, $transport);
        } catch (Throwable $exception) {
            $this->markQuotation($quotation, 'failed');
            $this->markEmailLogFailed($exception);
            $this->recordDelivery($quotation, 'failed', $recipient, 'Email failed: '.$exception->getMessage(), $transport);
            report($exception);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->markEmailLogFailed($exception);
    }

    private function markQuotation(Quotation $quotation, string $status): void
    {
        DB::transaction(function () use ($quotation, $status): void {
            if ($status === 'sent') {
                $quotation->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $quotation->quoteRequest?->update([
                    'status' => QuoteRequest::STATUS_EMAILED,
                ]);

                return;
            }

            $quotation->update([
                'status' => 'draft',
                'sent_at' => null,
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_EMAIL_FAILED,
            ]);
        });
    }

    private function recordDelivery(Quotation $quotation, string $status, string $recipient, string $message, string $transport): void
    {
        if (! Schema::hasTable('email_delivery_logs')) {
            return;
        }

        $now = now();
        $data = [
            'form_type' => 'quotation',
            'recipient_email' => Str::limit($recipient, 190, ''),
            'status' => $status,
            'direction' => 'client',
            'subject' => Str::limit($this->subject, 190, ''),
            'transport' => Str::limit($transport, 50, ''),
            'response_message' => Str::limit($message, 1000, ''),
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

    private function markEmailLogSent(): void
    {
        $emailLog = $this->emailLog();

        if (! $emailLog) {
            return;
        }

        $emailLog->increment('attempts');
        $emailLog->update([
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
            'failed_reason' => null,
        ]);
    }

    private function markEmailLogFailed(Throwable $exception): void
    {
        $emailLog = $this->emailLog();

        if (! $emailLog) {
            return;
        }

        $emailLog->increment('attempts');
        $emailLog->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
        ]);
    }

    private function emailLog(): ?EmailLog
    {
        return $this->emailLogId ? EmailLog::query()->find($this->emailLogId) : null;
    }

    private function mailTransportName(): string
    {
        $mailer = (string) config('mail.default', '');
        $transport = config("mail.mailers.{$mailer}.transport");

        return (string) ($transport ?: $mailer ?: 'unknown');
    }

    private function ensureDeliverableMailTransport(string $transport): void
    {
        if (in_array(Str::lower($transport), ['array', 'log'], true)) {
            throw new RuntimeException(
                'Quotation email failed: MAIL_MAILER='.$transport
                .' only stores email locally. Configure smtp, resend, postmark, mailgun, or ses before marking quotes as emailed.'
            );
        }
    }

    private function validatedSentMessageId(mixed $sentMessage): string
    {
        if (! $sentMessage instanceof SentMessage) {
            throw new RuntimeException('Quotation email failed: mail transport did not confirm that the message was accepted.');
        }

        $messageId = trim((string) $sentMessage->getMessageId());

        if ($messageId === '') {
            throw new RuntimeException('Quotation email failed: mail transport accepted the message without a delivery message ID.');
        }

        return $messageId;
    }
}
