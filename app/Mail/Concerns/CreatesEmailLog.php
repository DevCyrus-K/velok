<?php

namespace App\Mail\Concerns;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

trait CreatesEmailLog
{
    protected ?EmailLog $emailLog = null;

    protected function trackingToken(Model $model, ?string $recipient, string $subject): ?string
    {
        if ($this->emailLog) {
            return $this->emailLog->tracking_token;
        }

        if (! $recipient || ! filter_var($recipient, FILTER_VALIDATE_EMAIL) || ! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            $this->emailLog = EmailLog::create([
                'emailable_type' => $model::class,
                'emailable_id' => $model->getKey(),
                'recipient_email' => Str::limit(Str::lower($recipient), 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        return $this->emailLog->tracking_token;
    }
}
