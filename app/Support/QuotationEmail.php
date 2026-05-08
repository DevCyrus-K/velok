<?php

namespace App\Support;

use App\Jobs\SendQuotationEmailJob;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            .'- Total Amount: KES '.number_format((float) $quotation->quote_amount, 2)."\n\n"
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
    public function queue(Quotation $quotation, array $payload, ?User $user = null): array
    {
        $quotation->loadMissing('quoteRequest');
        $recipient = Str::lower(trim($payload['recipient_email']));
        $subject = (string) Str::of($payload['subject'])->squish();
        $message = trim($payload['message']);
        $sentAt = now();
        $emailLog = $this->createEmailLog($quotation, $recipient, $subject);

        try {
            $this->ensureDeliverableMailTransport();
        } catch (Throwable $exception) {
            $this->markFailed($quotation);
            $this->updateEmailLogFailure($emailLog, $exception);
            $this->recordFailure($quotation, $recipient, $subject, $exception);

            throw $exception;
        }

        DB::transaction(function () use ($quotation, $sentAt): void {
            $quotation->update([
                'status' => 'sent',
                'sent_at' => $sentAt,
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_EMAILED,
            ]);
        });

        try {
            SendQuotationEmailJob::dispatch(
                $quotation->id,
                $recipient,
                $subject,
                $message,
                (bool) $payload['attach_pdf'],
                $user?->getKey(),
                $emailLog?->getKey(),
            )->onQueue('emails');
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

    private function recordFailure(Quotation $quotation, string $recipient, string $subject, Throwable $exception): void
    {
        if (! Schema::hasTable('email_delivery_logs')) {
            return;
        }

        $mailer = (string) config('mail.default', '');
        $transport = (string) (config("mail.mailers.{$mailer}.transport") ?: $mailer ?: 'unknown');
        $now = now();
        $data = [
            'form_type' => 'quotation',
            'recipient_email' => Str::limit($recipient, 190, ''),
            'status' => 'failed',
            'direction' => 'client',
            'subject' => Str::limit($subject, 190, ''),
            'transport' => Str::limit($transport, 50, ''),
            'response_message' => Str::limit('Email failed: '.$exception->getMessage(), 1000, ''),
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

    private function createEmailLog(Quotation $quotation, string $recipient, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return $quotation->emailLogs()->create([
                'recipient_email' => Str::limit($recipient, 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_QUEUED,
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
}
