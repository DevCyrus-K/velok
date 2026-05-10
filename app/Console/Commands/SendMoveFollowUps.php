<?php

namespace App\Console\Commands;

use App\Mail\MoveFollowUpMail;
use App\Models\AppSetting;
use App\Models\Quotation;
use App\Services\MailConfigService;
use App\Support\BookingFlow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMoveFollowUps extends Command
{
    protected $signature = 'moves:send-followups';

    protected $description = 'Send post-move follow-up messages for confirmed bookings.';

    public function handle(): int
    {
        MailConfigService::apply();
        $yesterday = now()->subDay()->toDateString();

        $quotes = Quotation::query()
            ->with('quoteRequest')
            ->where('status', Quotation::STATUS_APPROVED)
            ->whereDate('move_date', $yesterday)
            ->where('deposit_paid', true)
            ->get();

        foreach ($quotes as $quote) {
            $preference = $quote->contact_preference;

            if (in_array($preference, ['email', 'both'], true)) {
                Mail::to($quote->customer_email)->send(new MoveFollowUpMail($quote));
            }

            if (in_array($preference, ['whatsapp', 'both'], true)) {
                $reviewLink = AppSetting::value('company', 'review_link', '');
                $message = "Hello {$quote->customer_name}! 🏠\n\n"
                    ."Hope you are settling in well!\n\n"
                    ."We would love to hear about your experience with *".config('app.name')."* ⭐\n\n"
                    ."Leave a review:\n{$reviewLink}\n\n"
                    ."Refer a friend and get *10% OFF* your next move!\n\n"
                    ."Thank you for choosing us!\n"
                    ."*".config('app.name')." Team* 🚛";

                $quote->update([
                    'followup_whatsapp_url' => app(BookingFlow::class)->whatsappUrl($quote->customer_phone, $message),
                ]);
            }

            $quote->logStage(
                'FOLLOWUP_SENT',
                'Follow-up message sent after move completion',
                'system',
                null,
                null,
                $preference
            );
        }

        $this->info("Follow-ups sent for {$quotes->count()} moves.");

        return self::SUCCESS;
    }
}
