<?php

namespace App\Http\Requests\Auth;

use App\Mail\AccountLockedMail;
use App\Models\User;
use App\Services\MailConfigService;
use App\Support\EmailLogRecorder;
use RuntimeException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LoginRequest extends FormRequest
{
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 900;
    private const IP_MAX_ATTEMPTS = 10;
    private const IP_DECAY_SECONDS = 3600;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim((string) $this->input('email')),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): Authenticatable
    {
        $this->ensureIsNotRateLimited();

        try {
            $credentials = $this->only('email', 'password');
            $user = Auth::getProvider()->retrieveByCredentials($credentials);
            $authenticated = $user && Auth::getProvider()->validateCredentials($user, $credentials);
        } catch (RuntimeException) {
            $authenticated = false;
            $user = null;
        }

        if (! $authenticated) {
            $this->recordFailedLogin();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::LOGIN_MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = max(1, (int) ceil($seconds / 60));

        throw ValidationException::withMessages([
            'email' => "Too many login attempts. Please try again in {$minutes} minutes.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return 'login:'.Str::lower((string) $this->input('email')).'|'.$this->ip();
    }

    private function recordFailedLogin(): void
    {
        $attempts = RateLimiter::hit($this->throttleKey(), self::LOGIN_DECAY_SECONDS);
        $ipAttempts = RateLimiter::hit($this->ipThrottleKey(), self::IP_DECAY_SECONDS);

        if ($attempts >= self::LOGIN_MAX_ATTEMPTS) {
            $this->sendAccountLockedNotification();
        }

        if ($ipAttempts >= self::IP_MAX_ATTEMPTS) {
            throw new HttpResponseException($this->blockedResponse());
        }
    }

    private function ipThrottleKey(): string
    {
        return 'login-ip:'.$this->ip();
    }

    private function sendAccountLockedNotification(): void
    {
        $email = Str::lower(trim((string) $this->input('email')));

        if ($email === '') {
            return;
        }

        $notificationKey = 'login-lock-notified:'.$this->throttleKey();

        if (RateLimiter::tooManyAttempts($notificationKey, 1)) {
            return;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return;
        }

        RateLimiter::hit($notificationKey, self::LOGIN_DECAY_SECONDS);

        $subject = 'Security alert - '.config('app.name');
        $emailLog = app(EmailLogRecorder::class)->create($user->email, $subject, $user);

        try {
            MailConfigService::apply();
            Mail::to($user->email)->send(new AccountLockedMail($emailLog?->tracking_token));
            app(EmailLogRecorder::class)->sent($emailLog);
        } catch (Throwable $exception) {
            app(EmailLogRecorder::class)->failed($emailLog, $exception);
            Log::warning('Account lock email failed: '.$exception->getMessage(), [
                'user_id' => $user->getKey(),
            ]);
        }
    }

    private function blockedResponse()
    {
        if ($this->expectsJson()) {
            return response()->json([
                'message' => 'Access temporarily blocked. Try later.',
            ], 429);
        }

        return response()->view('errors.429', [
            'message' => 'Access temporarily blocked. Try later.',
        ], 429);
    }
}
