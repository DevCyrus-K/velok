<?php

namespace App\Mail;

use App\Mail\Concerns\CreatesEmailLog;
use App\Models\Invoice;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::SALES),
            subject: 'Payment received for invoice '.$this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        $subject = 'Payment received for invoice '.$this->invoice->invoice_number;

        return new Content(
            view: 'emails.payment-received',
            with: [
                'invoice' => $this->invoice,
                'company' => app(CompanyProfile::class)->data(),
                'trackingToken' => $this->trackingToken($this->invoice, $this->invoice->customer_email, $subject),
            ],
        );
    }
}
