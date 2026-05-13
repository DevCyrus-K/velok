<?php

namespace App\Mail;

use App\Models\Message;
use App\Services\StorageService;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
        private readonly string $senderRole = MailSender::INFO,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address($this->senderRole),
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

        if (! $path) {
            return [];
        }

        $contents = app(StorageService::class)->contents($path);

        if ($contents === null) {
            return [];
        }

        $attachment = Attachment::fromData(
            fn () => $contents,
            $this->attachmentOriginalName ?: $this->message->attachment_original_name ?: basename((string) $path)
        );

        if ($this->attachmentOriginalName || $this->message->attachment_original_name) {
            $attachment = $attachment->as($this->attachmentOriginalName ?: $this->message->attachment_original_name);
        }

        if ($this->attachmentMime || $this->message->attachment_mime) {
            $attachment = $attachment->withMime($this->attachmentMime ?: $this->message->attachment_mime);
        }

        return [$attachment];
    }
}
