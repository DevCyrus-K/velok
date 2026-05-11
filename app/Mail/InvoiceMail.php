<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\User;
use App\Support\CompanyProfile;
use App\Support\InvoiceAuthorization;
use App\Support\MailSender;
use App\Support\PaymentSettings;
use App\Support\PdfDocumentName;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    private readonly ?string $subjectOverride;

    public function __construct(
        public Invoice $invoice,
        ?string $subject = null,
        private readonly ?string $messageBody = null,
        private readonly bool $attachPdf = true,
        private readonly ?User $user = null,
        private readonly ?int $emailLogId = null,
    ) {
        $this->subjectOverride = $subject;
        $this->invoice->loadMissing(['items', 'quoteRequest.quotation']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::SALES),
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'company' => app(CompanyProfile::class)->data(),
                'messageBody' => $this->messageBody,
                'attachPdf' => $this->attachPdf,
                'trackingToken' => $this->trackingToken(),
            ],
        );
    }

    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        return [
            Attachment::fromData(
                fn (): string => Pdf::loadView('invoices.pdf', [
                    'invoice' => $this->invoice,
                    'company' => app(CompanyProfile::class)->data(),
                    'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
                    'paymentMethods' => app(PaymentSettings::class)->methodsForInvoice($this->invoice),
                    'thankYouMessage' => app(CompanyProfile::class)->thankYouMessage(),
                    'authorization' => app(InvoiceAuthorization::class)->data($this->invoice, app(CompanyProfile::class)->data(), $this->user),
                    'signatureDataUri' => app(UserSignature::class)->dataUri($this->user?->signaturePath()),
                    'user' => $this->user,
                ])->setPaper('a4', 'portrait')
                    ->setOptions([
                        'dpi' => 150,
                        'enable_html5_parser' => true,
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'Inter',
                    ])
                    ->output(),
                $this->attachmentName(),
            )->withMime('application/pdf'),
        ];
    }

    public function subjectLine(): string
    {
        $companyName = trim((string) (app(CompanyProfile::class)->data()['name'] ?? '')) ?: 'Company';

        return $this->subjectOverride ?: 'Invoice ' . $this->invoice->invoice_number . ' from ' . $companyName;
    }

    private function attachmentName(): string
    {
        return app(PdfDocumentName::class)->invoiceFilename($this->invoice);
    }

    private function trackingToken(): ?string
    {
        if (! $this->emailLogId) {
            return null;
        }

        return \App\Models\EmailLog::query()
            ->whereKey($this->emailLogId)
            ->value('tracking_token');
    }
}
