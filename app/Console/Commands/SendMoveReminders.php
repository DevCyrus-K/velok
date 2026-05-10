<?php

namespace App\Console\Commands;

use App\Mail\MoveReminderMail;
use App\Models\Quotation;
use App\Services\MailConfigService;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMoveReminders extends Command
{
    protected $signature = 'moves:send-reminders';

    protected $description = 'Send 24-hour move reminders for confirmed bookings.';

    public function handle(): int
    {
        MailConfigService::apply();
        $tomorrow = now()->addDay()->toDateString();

        $quotes = Quotation::query()
            ->with('quoteRequest')
            ->where('status', Quotation::STATUS_APPROVED)
            ->whereDate('move_date', $tomorrow)
            ->where('deposit_paid', true)
            ->get();

        foreach ($quotes as $quote) {
            $preference = $quote->contact_preference;

            if (in_array($preference, ['email', 'both'], true)) {
                Mail::to($quote->customer_email)->send(new MoveReminderMail($quote));
            }

            if (in_array($preference, ['whatsapp', 'both'], true)) {
                $message = "Hello {$quote->customer_name}! 🚛\n\n"
                    ."*Move Day Reminder!*\n\n"
                    ."Your move is *TOMORROW* 📅\n\n"
                    ."📅 Date: ".$quote->move_date?->format('d M Y')."\n"
                    ."📍 Pickup: {$quote->pickup_location} - 8:00 AM\n"
                    ."📍 Drop-off: {$quote->dropoff_location}\n"
                    ."💰 Balance Due: KES ".number_format($quote->balanceDue(), 2)."\n\n"
                    ."Please ensure all items are packed and ready.\n\n"
                    ."Questions? Call us: ".(app(CompanyProfile::class)->data()['phone'] ?? '')."\n\n"
                    ."See you tomorrow!\n"
                    ."*".config('app.name')." Team*";

                $quote->update([
                    'reminder_whatsapp_url' => app(BookingFlow::class)->whatsappUrl($quote->customer_phone, $message),
                ]);
            }

            $quote->logStage(
                'REMINDER_SENT',
                'Move day reminder sent to customer',
                'system',
                null,
                null,
                $preference
            );
        }

        $this->info("Reminders sent for {$quotes->count()} moves.");

        return self::SUCCESS;
    }
}
