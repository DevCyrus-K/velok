<?php

namespace App\Mail;

use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountLockedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ?string $trackingToken = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'Security alert - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-locked',
            with: [
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
