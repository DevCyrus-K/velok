<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ServiceAgreementMail extends Mailable
{
    use SerializesModels;

    private readonly string $messageSubject;

    public function __construct(
        public Quotation $quotation,
        private readonly array $company,
        private readonly string $agreementPath,
        private readonly string $agreementFilename,
        string $subject,
        private readonly ?int $emailLogId = null,
    ) {
        $this->messageSubject = $subject;
        $this->quotation->loadMissing('quoteRequest');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->company['email'], $this->company['name']),
            subject: $this->messageSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-agreement',
            text: 'emails.service-agreement-text',
            with: $this->viewData(),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(Storage::disk('local')->path($this->agreementPath))
                ->as($this->agreementFilename)
                ->withMime('application/pdf'),
        ];
    }

    private function viewData(): array
    {
        return [
            'quotation' => $this->quotation,
            'quote' => $this->quotation->quoteRequest,
            'company' => $this->company,
            'trackingToken' => $this->trackingToken(),
        ];
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
