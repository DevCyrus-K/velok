<?php

namespace App\Mail;

use App\Mail\Concerns\CreatesEmailLog;
use App\Models\Quotation;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositReceivedMail extends Mailable implements ShouldQueue
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::SALES),
            subject: 'Deposit received - booking confirmed '.$this->quotation->reference,
        );
    }

    public function content(): Content
    {
        $subject = 'Deposit received - booking confirmed '.$this->quotation->reference;

        return new Content(
            view: 'emails.deposit-received',
            with: [
                'quotation' => $this->quotation,
                'company' => app(CompanyProfile::class)->data(),
                'trackingToken' => $this->trackingToken($this->quotation, $this->quotation->customer_email, $subject),
            ],
        );
    }
}
