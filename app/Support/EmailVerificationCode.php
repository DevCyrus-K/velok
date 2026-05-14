<?php

namespace App\Support;

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationCode
{
    private const CACHE_PREFIX = 'auth.email_verification_code.';
    private const TTL_MINUTES = 10;

    public function send(User $user, string $type = 'email_verification'): void
    {
        $code = $this->generateCode($type);

        Cache::put($this->cacheKey($user, $type), [
            'hash' => Hash::make($code),
            'type' => $type,
        ], now()->addMinutes(self::TTL_MINUTES));

        MailConfigService::apply();

        // Queue hardening: verification and reset codes now use the queued OTP mailable.
        Mail::to($user->email)->send(new OtpMail($code, $type, self::TTL_MINUTES));
    }

    public function verify(User $user, string $code, string $type = 'email_verification'): bool
    {
        $payload = Cache::get($this->cacheKey($user, $type));

        if (! is_array($payload) || ! isset($payload['hash'])) {
            return false;
        }

        $normalizedCode = preg_replace('/\D+/', '', $code) ?? '';

        if (strlen($normalizedCode) !== $this->digitsFor($type) || ! Hash::check($normalizedCode, $payload['hash'])) {
            return false;
        }

        Cache::forget($this->cacheKey($user, $type));

        return true;
    }

    public function hasPending(User $user, string $type = 'email_verification'): bool
    {
        return Cache::has($this->cacheKey($user, $type));
    }

    public function ttlMinutes(): int
    {
        return self::TTL_MINUTES;
    }

    public function digitsFor(string $type = 'email_verification'): int
    {
        return match ($type) {
            'password_reset' => 6,
            default => 5,
        };
    }

    private function cacheKey(User $user, string $type = 'email_verification'): string
    {
        return self::CACHE_PREFIX . $user->getKey() . '.' . $type;
    }

    private function generateCode(string $type): string
    {
        $digits = $this->digitsFor($type);
        $minimum = 10 ** ($digits - 1);
        $maximum = (10 ** $digits) - 1;

        return (string) random_int($minimum, $maximum);
    }
}
