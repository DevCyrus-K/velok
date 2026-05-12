<?php

namespace App\Support;

use App\Mail\QuotationMail;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class QuotationEmail
{
    public function defaultSubject(Quotation $quotation): string
    {
        $company = app(CompanyProfile::class)->data();
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';

        return 'Quotation '.$quotation->quoteRequest->reference().' from '.$companyName;
    }

    public function defaultMessage(Quotation $quotation, ?User $user = null): string
    {
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh()->loadMissing('quoteRequest');
        $company = app(CompanyProfile::class)->data();
        $quote = $quotation->quoteRequest;
        $validityDays = $quotation->validityDays() ?: 7;
        $jobTitle = $user?->job_title ?: 'Authorized Signatory';
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
        $companyPhone = trim((string) ($company['phone'] ?? ''));

        return 'Dear '.$quote->full_name.",\n\n"
            .'Please find attached your quotation '.$quote->reference().' from '.$companyName.".\n\n"
            ."Quotation Summary:\n"
            .'- Quote Number: '.$quote->reference()."\n"
            .'- Date: '.($quotation->quote_date?->format('d M Y') ?? now()->format('d M Y'))."\n"
            .'- Valid Until: '.($quotation->quote_valid_until?->format('d M Y') ?? now()->addDays($validityDays)->format('d M Y'))."\n"
            .'- Total Amount: KES '.number_format((float) $quotation->quote_amount, 2)."\n"
            .'- Deposit Required: KES '.number_format($quotation->depositAmount(), 2)."\n\n"
            ."Approve your quotation:\n"
            .route('quote.customer.approve', ['token' => $quotation->approval_token])."\n\n"
            ."Download PDF:\n"
            .route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token])."\n\n"
            .'This quotation is valid for '.$validityDays.' days from the date of issue. '
            ."Please do not hesitate to contact us if you have any questions.\n\n"
            .'Thank you for choosing '.$companyName.".\n\n"
            ."Best regards,\n"
            .($user?->name ?: $companyName)."\n"
            .$jobTitle."\n"
            .$companyPhone;
    }

    /**
     * @param array{recipient_email: string, subject: string, message: string, attach_pdf: bool} $payload
     * @return array{recipient_email: string, sent_at: string|null, sent_at_human: string|null, status: string, quote_status: string|null}
     */
    public function send(Quotation $quotation, array $payload, ?User $user = null): array
    {
        $quotation->loadMissing('quoteRequest');
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh()->loadMissing('quoteRequest');
        $recipient = Str::lower(trim($payload['recipient_email']));
        $subject = (string) Str::of($payload['subject'])->squish();
        $message = trim($payload['message']);
        $emailLog = $this->createEmailLog($quotation, $recipient, $subject);

        try {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Quotation email failed: recipient email address is invalid.');
            }

            $this->ensureDeliverableMailTransport();

            $sentMessage = Mail::to($recipient)->send(new QuotationMail(
                quotation: $quotation,
                messageBody: $message,
                subject: $subject,
                attachPdf: (bool) $payload['attach_pdf'],
                user: $user,
                emailLogId: $emailLog?->getKey(),
            ));

            $this->validatedSentMessageId($sentMessage);
            $this->markSent($quotation, $user);
            $this->markEmailLogSent($emailLog);
        } catch (Throwable $exception) {
            $this->markFailed($quotation);
            $this->updateEmailLogFailure($emailLog, $exception);

            throw $exception;
        }

        $quotation->refresh()->loadMissing('quoteRequest');

        return [
            'recipient_email' => $recipient,
            'sent_at' => $quotation->sent_at?->toISOString(),
            'sent_at_human' => $quotation->sent_at?->format('d M Y, h:i A'),
            'status' => $quotation->status,
            'quote_status' => $quotation->quoteRequest?->status,
        ];
    }

    private function ensureDeliverableMailTransport(): void
    {
        if ($this->mailerIsTestDouble()) {
            return;
        }

        $mailer = (string) config('mail.default', '');
        $transport = (string) (config("mail.mailers.{$mailer}.transport") ?: $mailer ?: 'unknown');

        if (in_array(Str::lower($transport), ['array', 'log'], true)) {
            throw new RuntimeException(
                'Quotation email failed: MAIL_MAILER='.$transport
                .' only stores email locally. Configure smtp, resend, postmark, mailgun, or ses before sending quotations.'
            );
        }
    }

    private function markFailed(Quotation $quotation): void
    {
        DB::transaction(function () use ($quotation): void {
            $quotation->update([
                'status' => 'draft',
                'sent_at' => null,
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_EMAIL_FAILED,
            ]);
        });
    }

    private function markSent(Quotation $quotation, ?User $user = null): void
    {
        DB::transaction(function () use ($quotation, $user): void {
            $quotation->update([
                'status' => Quotation::STATUS_SENT,
                'sent_at' => now(),
                'sent_via' => 'email',
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_EMAILED,
            ]);

            $quotation->logStage(
                'QUOTE_SENT',
                'Quotation sent via email',
                'admin',
                $user?->name,
                null,
                'email'
            );
        });
    }

    private function markEmailLogSent(?EmailLog $emailLog): void
    {
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

    private function createEmailLog(Quotation $quotation, string $recipient, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return $quotation->emailLogs()->create([
                'recipient_email' => Str::limit($recipient, 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function updateEmailLogFailure(?EmailLog $emailLog, Throwable $exception): void
    {
        if (! $emailLog) {
            return;
        }

        $emailLog->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
            'attempts' => $emailLog->attempts + 1,
        ]);
    }

    private function validatedSentMessageId(mixed $sentMessage): string
    {
        if ($this->mailerIsTestDouble() && $sentMessage === null) {
            return 'mail-test-double';
        }

        if (! $sentMessage instanceof SentMessage) {
            throw new RuntimeException('Quotation email failed: mail transport did not confirm that the message was accepted.');
        }

        $messageId = trim((string) $sentMessage->getMessageId());

        if ($messageId === '') {
            throw new RuntimeException('Quotation email failed: mail transport accepted the message without a delivery message ID.');
        }

        return $messageId;
    }

    private function mailerIsTestDouble(): bool
    {
        if (! app()->runningUnitTests()) {
            return false;
        }

        $mailer = Mail::getFacadeRoot();

        if (! is_object($mailer)) {
            return false;
        }

        if (is_a($mailer, \Illuminate\Support\Testing\Fakes\MailFake::class)) {
            return true;
        }

        return interface_exists(\Mockery\MockInterface::class)
            && $mailer instanceof \Mockery\MockInterface;
    }
}
