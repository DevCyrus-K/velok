<?php

namespace App\Mail;

use App\Models\Quotation;
use App\Models\User;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class QuotationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Quotation $quotation,
        private readonly string $messageBody,
        private readonly ?string $subject = null,
        private readonly bool $attachPdf = true,
        private readonly ?User $user = null,
        private readonly ?int $emailLogId = null,
    ) {
        $this->quotation->loadMissing('quoteRequest');
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
                'viewUrl' => route('quotations.show', $this->quotation),
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
                fn (): string => Pdf::loadView('quotes.pdf', [
                    'quote' => $this->quotation->quoteRequest,
                    'quotation' => $this->quotation,
                    'company' => app(CompanyProfile::class)->data(),
                    'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
                    'user' => $this->user,
                    'signatureDataUri' => app(UserSignature::class)->dataUri($this->user?->signaturePath()),
                    'authorization' => [
                        'name' => $this->user?->name ?: ($this->quotation->authorized_by ?: 'Pending'),
                        'job_title' => $this->user?->job_title ?: ($this->quotation->authorized_role ?: 'Authorized Signatory'),
                        'signature_path' => $this->user?->signaturePath() ?: $this->quotation->signature,
                        'is_complete' => app(UserSignature::class)->exists($this->user?->signaturePath()),
                        'date_label' => $this->quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
                        'prompt' => 'Signature not available',
                    ],
                ])->setPaper('a4')->output(),
                $this->attachmentName(),
            )->withMime('application/pdf'),
        ];
    }

    public function subjectLine(): string
    {
        $company = app(CompanyProfile::class)->data();
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';

        return $this->subject ?: 'Quotation '.$this->quotation->quoteRequest->reference().' from '.$companyName;
    }

    private function attachmentName(): string
    {
        $reference = Str::slug($this->quotation->quoteRequest->reference());
        $customerName = Str::slug((string) $this->quotation->quoteRequest->full_name);

        return 'Quotation-'.($reference !== '' ? $reference : $this->quotation->getKey()).'-'.($customerName !== '' ? $customerName : 'customer').'.pdf';
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
