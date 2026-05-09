<?php

namespace App\Mail;

use App\Models\User;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly bool $successful,
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly string $occurredAt,
        public readonly ?string $trackingToken = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: ($this->successful ? 'New login detected' : 'Someone tried to login').' - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-alert',
            with: [
                'user' => $this->user,
                'successful' => $this->successful,
                'ipAddress' => $this->ipAddress,
                'userAgent' => $this->userAgent,
                'occurredAt' => $this->occurredAt,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }
}
