<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $invoiceId,
        private readonly string $recipientEmail,
        private readonly string $subject,
        private readonly string $message,
        private readonly bool $attachPdf = true,
        private readonly ?int $userId = null,
        private readonly ?int $emailLogId = null,
    ) {}

    public function handle(): void
    {
        $invoice = Invoice::query()->with(['items', 'quoteRequest.quotation'])->find($this->invoiceId);

        if (! $invoice) {
            $this->markEmailLogFailed(new \RuntimeException('Invoice email failed: invoice record was not found.'));

            return;
        }

        $recipient = Str::lower(trim($this->recipientEmail));
        $user = $this->userId ? User::query()->find($this->userId) : null;

        try {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invoice email failed: recipient email address is invalid.');
            }

            Mail::to($recipient)->send(new InvoiceMail(
                invoice: $invoice,
                subject: $this->subject,
                messageBody: $this->message,
                attachPdf: $this->attachPdf,
                user: $user,
                emailLogId: $this->emailLogId,
            ));

            $this->markInvoice($invoice, Invoice::STATUS_SENT, $recipient);
            $this->markEmailLogSent();
            $this->recordDelivery($invoice, Invoice::STATUS_SENT, $recipient, 'Invoice email sent successfully.');
        } catch (Throwable $exception) {
            $this->markInvoice($invoice, Invoice::STATUS_FAILED, $recipient);
            $this->markEmailLogFailed($exception);
            $this->recordDelivery($invoice, Invoice::STATUS_FAILED, $recipient, 'Email failed: '.$exception->getMessage());
            report($exception);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->markEmailLogFailed($exception);
    }

    private function markInvoice(Invoice $invoice, string $status, string $recipient): void
    {
        $data = ['status' => $status];

        if (Schema::hasColumn('invoices', 'sent_to_email')) {
            $data['sent_to_email'] = $recipient;
        }

        if ($status === Invoice::STATUS_SENT && Schema::hasColumn('invoices', 'sent_at')) {
            $data['sent_at'] = now();
        }

        if ($status === Invoice::STATUS_SENT && Schema::hasColumn('invoices', 'sent_via')) {
            $data['sent_via'] = 'email';
        }

        $invoice->update($data);

        if ($status === Invoice::STATUS_SENT) {
            $invoice->logStage(
                'INVOICE_SENT',
                'Invoice sent via email',
                'admin',
                $this->userId ? User::query()->find($this->userId)?->name : null,
                null,
                'email'
            );
        }
    }

    private function recordDelivery(Invoice $invoice, string $status, string $recipient, string $message): void
    {
        if (! Schema::hasTable('email_delivery_logs')) {
            return;
        }

        $now = now();
        $data = [
            'form_type' => 'invoice',
            'recipient_email' => Str::limit($recipient, 190, ''),
            'status' => $status,
            'direction' => 'client',
            'subject' => Str::limit($this->subject, 190, ''),
            'transport' => Str::limit((string) config('mail.default'), 50, ''),
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
}
