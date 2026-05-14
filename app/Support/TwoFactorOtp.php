<?php

namespace App\Support;

use App\Mail\OtpMail;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TwoFactorOtp
{
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_MISSING = 'missing';

    private const REQUEST_LIMIT = 3;
    private const REQUEST_DECAY_SECONDS = 600;
    private const EXPIRES_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function send(User $user): void
    {
        $this->ensureCanRequest($user);

        $otp = $this->generateCode();

        $user->forceFill([
            'otp_code' => Crypt::encrypt($otp),
            'otp_expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'otp_attempts' => 0,
        ])->save();

        $subject = 'Your verification code — '.config('app.name');
        $emailLog = $this->createEmailLog($user, $subject);

        try {
            MailConfigService::apply();
            Mail::to($user->email)->send(new OtpMail(
                $otp,
                'two_factor',
                self::EXPIRES_MINUTES,
                $emailLog?->tracking_token,
            ));
            $this->markEmailLogSent($emailLog);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
            Log::error('OTP email failed: '.$exception->getMessage(), [
                'user_id' => $user->getKey(),
                'type' => 'two_factor',
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    public function verify(User $user, string $code): string
    {
        $normalizedCode = preg_replace('/\D+/', '', $code) ?? '';

        if (! $user->otp_code || ! $user->otp_expires_at) {
            return self::STATUS_MISSING;
        }

        if ($user->otp_expires_at->isPast()) {
            $this->clear($user);

            return self::STATUS_EXPIRED;
        }

        try {
            $expectedCode = (string) Crypt::decrypt($user->otp_code);
        } catch (Throwable) {
            $this->clear($user);

            return self::STATUS_EXPIRED;
        }

        if (hash_equals($expectedCode, $normalizedCode)) {
            $this->clear($user);

            return self::STATUS_VALID;
        }

        $attempts = (int) $user->otp_attempts + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->clear($user);

            return self::STATUS_LOCKED;
        }

        $user->forceFill(['otp_attempts' => $attempts])->save();

        return self::STATUS_INVALID;
    }

    public function clear(User $user): void
    {
        $user->forceFill([
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ])->save();
    }

    public function requestAvailableIn(User $user): int
    {
        return RateLimiter::availableIn($this->requestKey($user));
    }

    public function maxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }

    private function ensureCanRequest(User $user): void
    {
        $key = $this->requestKey($user);

        if (RateLimiter::tooManyAttempts($key, self::REQUEST_LIMIT)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

            throw ValidationException::withMessages([
                'otp' => "Too many OTP requests. Please try again in {$minutes} minutes.",
            ]);
        }

        RateLimiter::hit($key, self::REQUEST_DECAY_SECONDS);
    }

    private function requestKey(User $user): string
    {
        return 'otp-request:'.Str::lower((string) $user->email);
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function createEmailLog(User $user, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return EmailLog::query()->create([
                'emailable_type' => User::class,
                'emailable_id' => $user->getKey(),
                'recipient_email' => Str::limit(Str::lower(trim((string) $user->email)), 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
                'attempts' => 1,
                'sent_at' => null,
            ]);
        } catch (Throwable $exception) {
            Log::error('OTP email log creation failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return null;
        }
    }

    private function markEmailLogSent(?EmailLog $emailLog): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
            'failed_reason' => null,
        ]);
    }

    private function markEmailLogFailed(?EmailLog $emailLog, Throwable $exception): void
    {
        $emailLog?->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
        ]);
    }
}
