<?php

namespace App\Mail;

use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $type = 'two_factor',
        public readonly int $ttlMinutes = 10,
        public readonly ?string $trackingToken = null,
    )
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: $this->subjectLine(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'digits' => str_split($this->otp),
                'type' => $this->type,
                'ttlMinutes' => $this->ttlMinutes,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }

    public function subjectLine(): string
    {
        return match ($this->type) {
            'email_verification' => 'Verify your email - '.config('app.name'),
            'password_reset' => 'Your password reset code - '.config('app.name'),
            default => 'Your verification code — '.config('app.name'),
        };
    }
}
