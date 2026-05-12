<?php

namespace App\Mail;

use App\Models\Quotation;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteApprovedAdminMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Quotation $quotation, private readonly ?string $trackingToken = null) {}

    public function envelope(): Envelope
    {
        $clientName = trim((string) ($this->quotation->approved_by_name ?: $this->quotation->customer_name)) ?: 'Client';

        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'Quote approved by '.$clientName,
        );
    }

    public function content(): Content
    {
        $company = app(CompanyProfile::class)->data();

        return new Content(
            view: 'emails.quote-approved-admin',
            with: [
                'quotation' => $this->quotation,
                'company' => $company,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
