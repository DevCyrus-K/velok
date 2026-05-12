<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Invoice $invoice, private readonly ?string $trackingToken = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::SALES),
            subject: 'Payment received for invoice '.$this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-received',
            with: [
                'invoice' => $this->invoice,
                'company' => app(CompanyProfile::class)->data(),
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
