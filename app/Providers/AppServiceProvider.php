<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Services\StorageService;
use App\Support\TopbarData;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(TopbarData $topbarData): void
    {
        app(StorageService::class)->validateStorageConfiguration();
        $this->configureRateLimiting();
        $this->applyRuntimeSettings();

        View::composer('layouts.partials.topbar', function ($view) use ($topbarData): void {
            $payload = $topbarData->forUser(Auth::user());

            $view->with('topbarUser', $payload['user']);
            $view->with('topbarNotifications', $payload['notifications']);
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            return $user
                ? Limit::perMinute(60)->by('user:'.$user->getAuthIdentifier())
                : Limit::perMinute(20)->by('guest:'.$request->ip());
        });
    }

    private function applyRuntimeSettings(): void
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $email = AppSetting::groupValues('email');

        if (($email['enabled'] ?? '0') === '1') {
            config([
                'mail.from.address' => $email['from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $email['from_name'] ?? config('mail.from.name'),
            ]);

            if (($email['provider'] ?? 'smtp') === 'resend') {
                config([
                    'mail.default' => 'resend',
                    'services.resend.key' => AppSetting::value('email', 'resend_api_key'),
                ]);
            } else {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $email['smtp_host'] ?? 'smtp-relay.brevo.com',
                    'mail.mailers.smtp.port' => (int) ($email['smtp_port'] ?? 587),
                    'mail.mailers.smtp.username' => $email['smtp_username'] ?? null,
                    'mail.mailers.smtp.password' => AppSetting::value('email', 'smtp_password'),
                    'mail.mailers.smtp.scheme' => ($email['smtp_encryption'] ?? 'tls') === 'ssl' ? 'smtps' : null,
                    'mail.mailers.smtp.encryption' => $email['smtp_encryption'] ?? 'tls',
                ]);
            }
        }

        $analytics = AppSetting::groupValues('analytics');

        if (($analytics['enabled'] ?? '0') === '1') {
            config([
                'services.google_analytics.property_id' => $analytics['property_id'] ?? null,
                'services.google_analytics.credentials_path' => $analytics['credentials_path'] ?? null,
                'services.google_analytics.credentials_json' => AppSetting::value('analytics', 'credentials_json'),
            ]);
        }

        $sms = AppSetting::groupValues('sms');

        if (($sms['enabled'] ?? '0') === '1') {
            config([
                'services.sms.provider' => $sms['provider'] ?? 'africas_talking',
                'services.sms.sender_id' => $sms['sender_id'] ?? null,
                'services.sms.default_country_code' => $sms['default_country_code'] ?? '+254',
                'services.africas_talking.username' => $sms['africas_talking_username'] ?? null,
                'services.africas_talking.api_key' => AppSetting::value('sms', 'africas_talking_api_key'),
                'services.twilio.account_sid' => $sms['twilio_account_sid'] ?? null,
                'services.twilio.auth_token' => AppSetting::value('sms', 'twilio_auth_token'),
                'services.twilio.from' => $sms['twilio_from'] ?? null,
            ]);
        }
    }
}
