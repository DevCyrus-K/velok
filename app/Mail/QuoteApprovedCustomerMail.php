<?php

namespace App\Mail;

use App\Models\Quotation;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteApprovedCustomerMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Quotation $quotation, private readonly ?string $trackingToken = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'Your quotation is approved '.$this->quotation->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-approved-customer',
            with: [
                'quotation' => $this->quotation,
                'company' => app(CompanyProfile::class)->data(),
                'paymentMethods' => app(BookingFlow::class)->paymentMethodDisplays(),
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
