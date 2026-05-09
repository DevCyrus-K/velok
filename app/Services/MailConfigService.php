<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    public static function apply(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        $settings = AppSetting::groupValues('email');

        if ($settings === []) {
            return;
        }

        $host = self::setting($settings, 'mail_host', 'smtp_host', config('mail.mailers.smtp.host'));
        $port = self::setting($settings, 'mail_port', 'smtp_port', config('mail.mailers.smtp.port'));
        $encryption = self::setting($settings, 'mail_encryption', 'smtp_encryption', config('mail.mailers.smtp.encryption'));
        $username = self::setting($settings, 'mail_username', 'smtp_username', config('mail.mailers.smtp.username'));
        $password = self::setting($settings, 'mail_password', 'smtp_password', config('mail.mailers.smtp.password'));
        $provider = strtolower((string) ($settings['provider'] ?? 'smtp'));

        if ($encryption === 'none' || $encryption === '') {
            $encryption = null;
        }

        config([
            'mail.default' => $provider === 'log' ? 'log' : 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
        ]);

        app('mail.manager')->forgetMailers();
    }

    private static function setting(array $settings, string $primary, string $legacy, mixed $fallback): mixed
    {
        $value = $settings[$primary] ?? null;

        if ($value === null || $value === '') {
            $value = $settings[$legacy] ?? null;
        }

        return ($value === null || $value === '') ? $fallback : $value;
    }
}
