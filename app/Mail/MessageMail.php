<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Message $message,
        private readonly ?string $body = null,
        private readonly ?string $emailSubject = null,
        private readonly ?string $trackingToken = null,
        private readonly ?string $attachmentPath = null,
        private readonly ?string $attachmentOriginalName = null,
        private readonly ?string $attachmentMime = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject ?: $this->message->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.message',
            with: [
                'messageRecord' => $this->message,
                'body' => $this->body ?: $this->message->message,
                'trackingToken' => $this->trackingToken,
            ],
        );
    }

    public function attachments(): array
    {
        $path = $this->attachmentPath ?: $this->message->attachment_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return [];
        }

        $attachment = Attachment::fromPath(Storage::disk('local')->path($path));

        if ($this->attachmentOriginalName || $this->message->attachment_original_name) {
            $attachment = $attachment->as($this->attachmentOriginalName ?: $this->message->attachment_original_name);
        }

        if ($this->attachmentMime || $this->message->attachment_mime) {
            $attachment = $attachment->withMime($this->attachmentMime ?: $this->message->attachment_mime);
        }

        return [$attachment];
    }
}
