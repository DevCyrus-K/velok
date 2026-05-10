<?php

namespace App\Mail;

use App\Mail\Concerns\CreatesEmailLog;
use App\Models\Quotation;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteApprovedAdminMail extends Mailable
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'Quotation approved '.$this->quotation->reference,
        );
    }

    public function content(): Content
    {
        $subject = 'Quotation approved '.$this->quotation->reference;
        $company = app(CompanyProfile::class)->data();

        return new Content(
            view: 'emails.quote-approved-admin',
            with: [
                'quotation' => $this->quotation,
                'company' => $company,
                'trackingToken' => $this->trackingToken($this->quotation, $company['email'] ?? null, $subject),
            ],
        );
    }
}
