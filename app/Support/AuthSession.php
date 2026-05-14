<?php

namespace App\Support;

use App\Mail\LoginAlertMail;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class AuthSession
{
    public function __construct(private readonly TopbarData $topbarData)
    {
    }

    public function login(Request $request, User $user, bool $remember = false, bool $sendLoginAlert = true): void
    {
        Auth::guard('web')->login($user, $remember);

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => Str::limit((string) $request->ip(), 45, ''),
        ])->save();

        $this->refresh($request, $user);
        app(NotificationLogger::class)->loginSucceeded($user, $request);

        if ($sendLoginAlert) {
            $this->sendLoginAlert($request, $user);
        }
    }

    public function refresh(Request $request, User $user): void
    {
        $user->refresh();

        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_email', $user->email);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_avatar', $this->topbarData->avatarUrl($user));
        $request->session()->put('user_avatar_initials', $this->topbarData->initials($user));
        $request->session()->put('user_has_avatar', $this->topbarData->hasAvatar($user));
    }

    private function sendLoginAlert(Request $request, User $user): void
    {
        $subject = 'New login detected - '.config('app.name');
        $emailLog = app(EmailLogRecorder::class)->create($user->email, $subject, $user);

        try {
            MailConfigService::apply();

            Mail::to($user->email)->send(new LoginAlertMail(
                user: $user,
                successful: true,
                ipAddress: Str::limit((string) $request->ip(), 45, ''),
                userAgent: Str::limit((string) $request->userAgent(), 180, ''),
                occurredAt: now()->format('M j, Y g:i A'),
                trackingToken: $emailLog?->tracking_token,
            ));
            app(EmailLogRecorder::class)->sent($emailLog);
        } catch (Throwable $exception) {
            app(EmailLogRecorder::class)->failed($emailLog, $exception);
            Log::error('Login alert email failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
