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
            $log->update([
                'status' => EmailLog::STATUS_OPENED,
                'opened_at' => $log->opened_at ?: now(),
            ]);
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
