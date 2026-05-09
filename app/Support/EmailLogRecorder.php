<?php

namespace App\Support;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class EmailLogRecorder
{
    public function create(string $recipientEmail, string $subject, ?Model $relatedModel = null): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return EmailLog::create([
                'emailable_type' => $relatedModel ? get_class($relatedModel) : null,
                'emailable_id' => $relatedModel?->getKey(),
                'recipient_email' => Str::limit(Str::lower(trim($recipientEmail)), 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
                'attempts' => 1,
                'sent_at' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function sent(?EmailLog $emailLog): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function failed(?EmailLog $emailLog, Throwable $exception): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => $exception->getMessage(),
        ]);
    }
}
