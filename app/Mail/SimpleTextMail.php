<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimpleTextMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{address: string, name: string}  $from
     */
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $body,
        private readonly array $from,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->from['address'], $this->from['name']),
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.simple-text',
            with: [
                // Queue hardening: simple operational emails render through a queued mailable.
                'body' => $this->body,
            ],
        );
    }
}
