<?php

namespace App\Mail;

use App\Mail\Concerns\CreatesEmailLog;
use App\Models\QuoteRequest;
use App\Support\CompanyProfile;
use App\Support\MailSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestAdminNotificationMail extends Mailable implements ShouldQueue
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest, public ?string $recipient = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::INFO),
            subject: 'New moving quote request '.$this->quoteRequest->reference(),
        );
    }

    public function content(): Content
    {
        $subject = 'New moving quote request '.$this->quoteRequest->reference();
        $company = app(CompanyProfile::class)->data();

        return new Content(
            view: 'emails.quote-request-admin-notification',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'company' => $company,
                'trackingToken' => $this->trackingToken($this->quoteRequest, $this->recipient ?: ($company['email'] ?? null), $subject),
            ],
        );
    }
}
