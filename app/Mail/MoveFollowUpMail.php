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

class MoveFollowUpMail extends Mailable implements ShouldQueue
{
    use CreatesEmailLog;
    use Queueable;
    use SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: app(MailSender::class)->address(MailSender::NOREPLY),
            subject: 'How was your move?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.move-follow-up',
            with: [
                'quotation' => $this->quotation,
                'company' => app(CompanyProfile::class)->data(),
                'reviewLink' => \App\Models\AppSetting::value('company', 'review_link', ''),
                'trackingToken' => $this->trackingToken($this->quotation, $this->quotation->customer_email, 'How was your move?'),
            ],
        );
    }
}
