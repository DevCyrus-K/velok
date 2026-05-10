<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $log = EmailLog::query()
            ->where('tracking_token', $token)
            ->first();

        if ($log) {
            $firstOpen = ! $log->opened_at;
            $log->update([
                'status' => EmailLog::STATUS_OPENED,
                'opened_at' => $log->opened_at ?: now(),
            ]);

            if ($firstOpen && $log->emailable && method_exists($log->emailable, 'logStage')) {
                $log->emailable->logStage(
                    'EMAIL_OPENED',
                    'Customer opened an email',
                    'customer',
                    null,
                    request()->ip(),
                    'email',
                    ['subject' => $log->subject]
                );
            }
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
