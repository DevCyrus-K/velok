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

class QuoteRequestConfirmationMail extends Mailable implements ShouldQueue
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'We received your quote request '.$this->quoteRequest->reference(),
        );
    }

    public function content(): Content
    {
        $subject = 'We received your quote request '.$this->quoteRequest->reference();

        return new Content(
            view: 'emails.quote-request-confirmation',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'company' => app(CompanyProfile::class)->data(),
                'trackingToken' => $this->trackingToken($this->quoteRequest, $this->quoteRequest->email, $subject),
            ],
        );
    }
}
