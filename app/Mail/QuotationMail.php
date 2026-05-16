<?php

namespace App\Mail;

use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\User;
use App\Services\StorageService;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use App\Support\PdfDocumentName;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class QuotationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private readonly ?string $subjectOverride;

    public function __construct(
        public Quotation $quotation,
        private readonly string $messageBody,
        ?string $subject = null,
        private readonly bool $attachPdf = true,
        private readonly ?User $user = null,
        private readonly ?int $emailLogId = null,
    ) {
        $this->subjectOverride = $subject;
        $this->quotation->loadMissing('quoteRequest');
        app(BookingFlow::class)->ensureQuotationTokens($this->quotation);
        $this->quotation->refresh()->loadMissing('quoteRequest');
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
        $company = app(CompanyProfile::class)->data();

        return new Content(
            view: 'emails.quotation',
            with: [
                'quotation' => $this->quotation,
                'quote' => $this->quotation->quoteRequest,
                'company' => $company,
                'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
                'messageBody' => $this->messageBody,
                'attachPdf' => $this->attachPdf,
                'viewUrl' => route('quote.customer.approve', ['token' => $this->quotation->approval_token]),
                'pdfUrl' => route('quote.pdf.download', ['id' => $this->quotation->id, 'token' => $this->quotation->pdf_token]),
                'trackingToken' => $this->trackingToken(),
            ],
        );
    }

    public function attachments(): array
    {
        if (! $this->attachPdf) {
            return [];
        }

        $filename = $this->attachmentName();
        $contents = Pdf::loadView('quotes.pdf', [
            'quote' => $this->quotation->quoteRequest,
            'quotation' => $this->quotation,
            'company' => app(CompanyProfile::class)->data(),
            'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
            'user' => $this->user,
            'signatureDataUri' => app(UserSignature::class)->dataUri($this->user?->signaturePath()),
            'paymentMethods' => app(BookingFlow::class)->paymentMethodDisplays(),
            'thankYouMessage' => app(CompanyProfile::class)->thankYouMessage(),
            'approvalUrl' => route('quote.customer.approve', ['token' => $this->quotation->approval_token]),
            'pdfUrl' => route('quote.pdf.download', ['id' => $this->quotation->id, 'token' => $this->quotation->pdf_token]),
            'authorization' => [
                'name' => $this->user?->name ?: ($this->quotation->authorized_by ?: 'Pending'),
                'job_title' => $this->user?->job_title ?: ($this->quotation->authorized_role ?: 'Authorized Signatory'),
                'signature_path' => $this->user?->signaturePath() ?: $this->quotation->signature,
                'is_complete' => app(UserSignature::class)->exists($this->user?->signaturePath()),
                'date_label' => $this->quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
                'prompt' => 'Signature not available',
            ],
        ])->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'enable_html5_parser' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Inter',
            ])
            ->output();
        
        try {
            $uploaded = app(StorageService::class)->uploadGeneratedPdf($contents, $filename, 'quotes');

            if (Schema::hasColumn('quotations', 'quote_pdf_storage_key')) {
                $this->quotation->update([
                    'quote_pdf_storage_key' => $uploaded['key'],
                    'quote_pdf_storage_file_id' => $uploaded['fileId'],
                    'quote_pdf_storage_url' => $uploaded['url'],
                    'pdf_storage_key' => $uploaded['key'],
                    'pdf_storage_file_id' => $uploaded['fileId'],
                    'pdf_storage_url' => $uploaded['url'],
                ]);
            }
        } catch (\Exception $e) {
            // Fallback: log error but continue with email
            \Log::warning("B2 upload failed for quotation {$this->quotation->id} in email: {$e->getMessage()}");
        }

        return [
            Attachment::fromData(fn () => $contents, $filename)
                ->as($filename)
                ->withMime('application/pdf'),
        ];
    }

    public function subjectLine(): string
    {
        $company = app(CompanyProfile::class)->data();
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';

        return $this->subjectOverride ?: 'Quotation '.$this->quotation->quoteRequest->reference().' from '.$companyName;
    }

    private function attachmentName(): string
    {
        return app(PdfDocumentName::class)->quotationFilename($this->quotation);
    }

    private function trackingToken(): ?string
    {
        if (! $this->emailLogId) {
            return null;
        }

        return EmailLog::query()
            ->whereKey($this->emailLogId)
            ->value('tracking_token');
    }
}
