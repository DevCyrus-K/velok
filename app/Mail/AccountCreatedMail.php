<?php

namespace App\Mail;

use App\Models\User;
use App\Support\MailSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ?string $trackingToken = null,
    )
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'Your account was created successfully - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-created',
            with: [
                'user' => $this->user,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
