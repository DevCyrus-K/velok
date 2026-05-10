<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Support\CompanyProfile;
use App\Support\PaymentSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const SECRET_KEYS = [
        'payments' => PaymentSettings::SECRET_KEYS,
        'email' => ['smtp_password', 'brevo_api_key', 'resend_api_key'],
        'analytics' => ['credentials_json'],
        'sms' => ['africas_talking_api_key', 'twilio_auth_token'],
    ];

    public function index(): View
    {
        return view('settings.index', [
            'settings' => $this->settings(),
            'secretStatus' => $this->secretStatus(),
            'apps' => $this->integrationCards(),
        ]);
    }

    public function apps(): View
    {
        return view('settings.apps', [
            'apps' => $this->integrationCards(),
            'settings' => $this->settings(),
            'secretStatus' => $this->secretStatus(),
        ]);
    }

    public function paymentSettings(): View
    {
        return view('settings.apps', [
            'apps' => $this->integrationCards(),
            'settings' => $this->settings(),
            'secretStatus' => $this->secretStatus(),
            'activeModal' => 'configurePaymentsApp',
        ]);
    }

    public function updatePayment(Request $request): RedirectResponse
    {
        $this->updatePayments($request);

        return redirect()
            ->route('settings.payment')
            ->with('toast-success', 'Payment settings saved.');
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        abort_unless(in_array($section, ['payments', 'invoice', 'email', 'analytics', 'sms'], true), 404);

        match ($section) {
            'payments' => $this->updatePayments($request),
            'invoice' => $this->updateInvoice($request),
            'email' => $this->updateEmail($request),
            'analytics' => $this->updateAnalytics($request),
            'sms' => $this->updateSms($request),
        };

        $target = $request->boolean('manage_apps')
            ? route('settings.apps') . '#managed-apps'
            : route('settings.index') . '#' . $section . '-settings';

        return redirect()
            ->to($target)
            ->with('toast-success', ucfirst($section) . ' settings saved.');
    }

    private function updatePayments(Request $request): void
    {
        $mpesaEnabled = $request->boolean('mpesa_enabled');
        $mpesaType = (string) $request->input('mpesa_type', 'till');
        $bankEnabled = $request->boolean('bank_enabled');

        $validated = $request->validate([
            'mpesa_type' => [$mpesaEnabled ? 'required' : 'nullable', Rule::in(['till', 'paybill', 'pochi'])],
            'mpesa_till_number' => [
                Rule::requiredIf($mpesaEnabled && $mpesaType === 'till' && ! AppSetting::hasStoredValue('payments', 'mpesa_till_number')),
                'nullable',
                'regex:/^\d+$/',
                'max:20',
            ],
            'mpesa_till_account_name' => [Rule::requiredIf($mpesaEnabled && $mpesaType === 'till'), 'nullable', 'string', 'max:120'],
            'mpesa_paybill_business_number' => [
                Rule::requiredIf($mpesaEnabled && $mpesaType === 'paybill' && ! AppSetting::hasStoredValue('payments', 'mpesa_paybill_business_number')),
                'nullable',
                'regex:/^\d+$/',
                'max:20',
            ],
            'mpesa_paybill_account_number' => [
                Rule::requiredIf($mpesaEnabled && $mpesaType === 'paybill' && ! AppSetting::hasStoredValue('payments', 'mpesa_paybill_account_number')),
                'nullable',
                'string',
                'max:80',
            ],
            'mpesa_paybill_account_name' => [Rule::requiredIf($mpesaEnabled && $mpesaType === 'paybill'), 'nullable', 'string', 'max:120'],
            'mpesa_pochi_phone' => [
                Rule::requiredIf($mpesaEnabled && $mpesaType === 'pochi' && ! AppSetting::hasStoredValue('payments', 'mpesa_pochi_phone')),
                'nullable',
                'regex:/^(07|01)\d{8}$/',
            ],
            'mpesa_pochi_registered_name' => [Rule::requiredIf($mpesaEnabled && $mpesaType === 'pochi'), 'nullable', 'string', 'max:120'],
            'cash_instruction' => ['nullable', 'string', 'max:1000'],
            'bank_name' => [Rule::requiredIf($bankEnabled), 'nullable', 'string', 'max:120'],
            'bank_account_name' => [Rule::requiredIf($bankEnabled), 'nullable', 'string', 'max:120'],
            'bank_account_number' => [
                Rule::requiredIf($bankEnabled && ! AppSetting::hasStoredValue('payments', 'bank_account_number')),
                'nullable',
                'regex:/^\d+$/',
                'max:40',
            ],
            'bank_branch' => [Rule::requiredIf($bankEnabled), 'nullable', 'string', 'max:120'],
            'bank_swift_code' => ['nullable', 'string', 'max:40'],
            'thank_you_message' => ['nullable', 'string', 'max:1500'],
        ]);

        $values = [
            'mpesa_enabled' => $request->boolean('mpesa_enabled') ? '1' : '0',
            'mpesa_type' => $validated['mpesa_type'] ?? 'till',
            'cash_enabled' => $request->boolean('cash_enabled') ? '1' : '0',
            'cash_instruction' => $this->cleanText($validated['cash_instruction'] ?? ''),
            'bank_enabled' => $request->boolean('bank_enabled') ? '1' : '0',
            'bank_name' => $this->cleanText($validated['bank_name'] ?? ''),
            'bank_account_name' => $this->cleanText($validated['bank_account_name'] ?? ''),
            'bank_branch' => $this->cleanText($validated['bank_branch'] ?? ''),
            'bank_swift_code' => $this->cleanText($validated['bank_swift_code'] ?? ''),
            'mpesa_till_account_name' => $this->cleanText($validated['mpesa_till_account_name'] ?? ''),
            'mpesa_paybill_account_name' => $this->cleanText($validated['mpesa_paybill_account_name'] ?? ''),
            'mpesa_pochi_registered_name' => $this->cleanText($validated['mpesa_pochi_registered_name'] ?? ''),
        ];

        AppSetting::setMany('payments', $values);
        $this->storePresentSecrets('payments', $validated);

        if (array_key_exists('thank_you_message', $validated)) {
            AppSetting::setValue(
                'invoice',
                'thank_you_message',
                $this->cleanText($validated['thank_you_message'] ?: CompanyProfile::DEFAULT_THANK_YOU_TEMPLATE),
            );
        }
    }

    private function updateInvoice(Request $request): void
    {
        $validated = $request->validate([
            'thank_you_message' => ['required', 'string', 'max:1500'],
        ]);

        AppSetting::setValue('invoice', 'thank_you_message', $this->cleanText($validated['thank_you_message']));
    }

    private function updateEmail(Request $request): void
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(['brevo', 'resend', 'smtp', 'log'])],
            'from_name' => ['nullable', 'string', 'max:120'],
            'from_address' => ['required', 'email', 'max:160'],
            'smtp_host' => ['nullable', 'string', 'max:160'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'smtp_username' => ['nullable', 'string', 'max:160'],
            'smtp_password' => ['nullable', 'string', 'max:500'],
            'brevo_api_key' => ['nullable', 'string', 'max:500'],
            'resend_api_key' => ['nullable', 'string', 'max:500'],
            'mail_from_messages_name' => ['nullable', 'string', 'max:120'],
            'mail_from_messages_address' => ['required', 'email', 'max:160'],
            'mail_from_noreply_name' => ['nullable', 'string', 'max:120'],
            'mail_from_noreply_address' => ['required', 'email', 'max:160'],
            'mail_from_invoices_name' => ['nullable', 'string', 'max:120'],
            'mail_from_invoices_address' => ['required', 'email', 'max:160'],
        ]);

        $values = [
            'enabled' => $request->boolean('enabled') ? '1' : '0',
            'provider' => $validated['provider'],
            'from_name' => $validated['from_name'] ?? '',
            'from_address' => $validated['from_address'],
            'smtp_host' => filled($validated['smtp_host'] ?? null) ? $validated['smtp_host'] : $this->defaultSmtpHost($validated['provider']),
            'smtp_port' => (string) ($validated['smtp_port'] ?? 587),
            'smtp_encryption' => $validated['smtp_encryption'] ?? 'tls',
            'smtp_username' => filled($validated['smtp_username'] ?? null) ? $validated['smtp_username'] : $this->defaultSmtpUsername($validated['provider']),
            'mail_from_messages_name' => $this->cleanText($validated['mail_from_messages_name'] ?? ''),
            'mail_from_messages_address' => Str::lower(trim($validated['mail_from_messages_address'])),
            'mail_from_noreply_name' => $this->cleanText($validated['mail_from_noreply_name'] ?? ''),
            'mail_from_noreply_address' => Str::lower(trim($validated['mail_from_noreply_address'])),
            'mail_from_invoices_name' => $this->cleanText($validated['mail_from_invoices_name'] ?? ''),
            'mail_from_invoices_address' => Str::lower(trim($validated['mail_from_invoices_address'])),
        ];

        AppSetting::setMany('email', $values);
        $this->storePresentSecrets('email', $validated);
    }

    private function updateAnalytics(Request $request): void
    {
        $validated = $request->validate([
            'property_id' => ['nullable', 'string', 'max:120'],
            'measurement_id' => ['nullable', 'string', 'max:120'],
            'credentials_path' => ['nullable', 'string', 'max:255'],
            'credentials_json' => ['nullable', 'string', 'max:20000'],
        ]);

        AppSetting::setMany('analytics', [
            'enabled' => $request->boolean('enabled') ? '1' : '0',
            'property_id' => $validated['property_id'] ?? '',
            'measurement_id' => $validated['measurement_id'] ?? '',
            'credentials_path' => $validated['credentials_path'] ?? '',
        ]);

        $this->storePresentSecrets('analytics', $validated);
    }

    private function updateSms(Request $request): void
    {
        $validated = $request->validate([
            'provider' => ['required', Rule::in(['africas_talking', 'twilio', 'none'])],
            'sender_id' => ['nullable', 'string', 'max:40'],
            'default_country_code' => ['nullable', 'string', 'max:10'],
            'africas_talking_username' => ['nullable', 'string', 'max:120'],
            'africas_talking_api_key' => ['nullable', 'string', 'max:500'],
            'twilio_account_sid' => ['nullable', 'string', 'max:160'],
            'twilio_auth_token' => ['nullable', 'string', 'max:500'],
            'twilio_from' => ['nullable', 'string', 'max:40'],
        ]);

        AppSetting::setMany('sms', [
            'enabled' => $request->boolean('enabled') ? '1' : '0',
            'provider' => $validated['provider'],
            'sender_id' => $validated['sender_id'] ?? '',
            'default_country_code' => $validated['default_country_code'] ?? '+254',
            'africas_talking_username' => $validated['africas_talking_username'] ?? '',
            'twilio_account_sid' => $validated['twilio_account_sid'] ?? '',
            'twilio_from' => $validated['twilio_from'] ?? '',
        ]);

        $this->storePresentSecrets('sms', $validated);
    }

    private function settings(): array
    {
        return [
            'payments' => app(PaymentSettings::class)->values(),
            'invoice' => AppSetting::groupValues('invoice', [
                'thank_you_message' => CompanyProfile::DEFAULT_THANK_YOU_TEMPLATE,
            ]),
            'email' => AppSetting::groupValues('email', [
                'enabled' => '0',
                'provider' => 'brevo',
                'from_name' => config('mail.from.name'),
                'from_address' => config('mail.from.address'),
                'smtp_host' => 'smtp-relay.brevo.com',
                'smtp_port' => '587',
                'smtp_encryption' => 'tls',
                'smtp_username' => '',
                'mail_from_messages_name' => config('mail.senders.info.name', config('mail.from.name')),
                'mail_from_messages_address' => config('mail.senders.info.address', 'info@kwikshiftmovers.co.ke'),
                'mail_from_noreply_name' => config('mail.senders.noreply.name', config('mail.from.name')),
                'mail_from_noreply_address' => config('mail.senders.noreply.address', 'noreply@kwikshiftmovers.co.ke'),
                'mail_from_invoices_name' => config('mail.senders.sales.name', config('mail.from.name')),
                'mail_from_invoices_address' => config('mail.senders.sales.address', 'sales@kwikshiftmovers.co.ke'),
            ]),
            'analytics' => AppSetting::groupValues('analytics', [
                'enabled' => '0',
                'property_id' => config('services.google_analytics.property_id', ''),
                'measurement_id' => '',
                'credentials_path' => config('services.google_analytics.credentials_path', ''),
            ]),
            'sms' => AppSetting::groupValues('sms', [
                'enabled' => '0',
                'provider' => 'africas_talking',
                'sender_id' => '',
                'default_country_code' => '+254',
                'africas_talking_username' => '',
                'twilio_account_sid' => '',
                'twilio_from' => '',
            ]),
        ];
    }

    private function secretStatus(): array
    {
        $status = [];

        foreach (self::SECRET_KEYS as $group => $keys) {
            foreach ($keys as $key) {
                $status[$group][$key] = AppSetting::hasStoredValue($group, $key);
            }
        }

        return $status;
    }

    private function integrationCards(): array
    {
        $settings = $this->settings();
        $secrets = $this->secretStatus();
        $emailProvider = $settings['email']['provider'] ?? 'smtp';
        $smsProvider = $settings['sms']['provider'] ?? 'none';
        $emailEnabled = ($settings['email']['enabled'] ?? '0') === '1';
        $smsEnabled = ($settings['sms']['enabled'] ?? '0') === '1';
        $hasSmtpCredentials = filled($settings['email']['smtp_host'] ?? null) && ($secrets['email']['smtp_password'] ?? false);
        $hasBrevoCredentials = $hasSmtpCredentials;
        $hasResendCredentials = ($secrets['email']['resend_api_key'] ?? false);
        $hasAfricaTalkingCredentials = filled($settings['sms']['africas_talking_username'] ?? null) && ($secrets['sms']['africas_talking_api_key'] ?? false);
        $hasTwilioCredentials = filled($settings['sms']['twilio_account_sid'] ?? null) && ($secrets['sms']['twilio_auth_token'] ?? false);

        return [
            [
                'name' => 'Payment Methods',
                'domain' => 'PayHero',
                'website' => 'payherokenya.com',
                'icon' => 'credit-card',
                'image' => '/images/apps/payhero.png',
                'description' => 'M-Pesa, bank transfer, card, and cash options are configured here. PayHero stays disconnected until real PayHero credentials exist.',
                'connected' => false,
                'status_label' => 'Not Connected',
                'status_class' => 'bg-light text-muted',
                'section' => 'payments',
                'modal_id' => 'configurePaymentsApp',
                'meta' => collect(['mpesa_enabled', 'bank_enabled', 'cash_enabled'])
                    ->contains(fn ($key) => ($settings['payments'][$key] ?? '0') === '1')
                    ? 'Manual payment methods active'
                    : 'No payment method active',
                'url' => route('settings.index') . '#payments-settings',
            ],
            [
                'name' => 'Email Delivery',
                'domain' => 'Brevo SMTP',
                'website' => 'brevo.com',
                'icon' => 'mail-check',
                'image' => '/images/apps/brevo.svg',
                'description' => 'Brevo SMTP settings for invoices, quotation emails, and customer messages.',
                'connected' => $emailEnabled && $emailProvider === 'brevo' && $hasBrevoCredentials,
                'section' => 'email',
                'provider' => 'brevo',
                'modal_id' => 'configureBrevoApp',
                'meta' => ($settings['email']['from_address'] ?? '') ?: 'No sender configured',
                'url' => route('settings.index') . '#email-settings',
            ],
            [
                'name' => 'Resend',
                'domain' => 'Email API',
                'website' => 'resend.com',
                'icon' => 'send',
                'image' => '/images/apps/resend.svg',
                'description' => 'Resend API credentials for transactional invoice and lead follow-up email.',
                'connected' => $emailEnabled && $emailProvider === 'resend' && $hasResendCredentials,
                'section' => 'email',
                'provider' => 'resend',
                'modal_id' => 'configureResendApp',
                'meta' => $hasResendCredentials ? 'API key saved' : 'API key required',
                'url' => route('settings.index') . '#email-settings',
            ],
            [
                'name' => 'Custom SMTP',
                'domain' => 'SMTP',
                'website' => '',
                'icon' => 'server-cog',
                'image' => null,
                'description' => 'Generic SMTP host, port, username, and password for any mail provider.',
                'connected' => $emailEnabled && $emailProvider === 'smtp' && $hasSmtpCredentials,
                'section' => 'email',
                'provider' => 'smtp',
                'modal_id' => 'configureSmtpApp',
                'meta' => ($settings['email']['smtp_host'] ?? '') ?: 'SMTP host required',
                'url' => route('settings.index') . '#email-settings',
            ],
            [
                'name' => 'Google Analytics',
                'domain' => 'Google',
                'website' => 'analytics.google.com',
                'icon' => 'chart-no-axes-combined',
                'image' => '/images/apps/google-analytics.svg',
                'description' => 'Analytics property and service account credentials for visitor reporting.',
                'connected' => ($settings['analytics']['enabled'] ?? '0') === '1'
                    && filled($settings['analytics']['property_id'] ?? null)
                    && (filled($settings['analytics']['credentials_path'] ?? null) || ($secrets['analytics']['credentials_json'] ?? false)),
                'section' => 'analytics',
                'modal_id' => 'configureAnalyticsApp',
                'meta' => ($settings['analytics']['property_id'] ?? '') ?: 'No property ID',
                'url' => route('settings.index') . '#analytics-settings',
            ],
            [
                'name' => 'SMS Messaging',
                'domain' => $this->smsProviderLabel($smsProvider),
                'website' => $this->smsProviderWebsite($smsProvider),
                'icon' => 'message-circle',
                'image' => $this->smsProviderImage($smsProvider),
                'description' => 'Africa\'s Talking or Twilio credentials for lead and booking SMS alerts.',
                'connected' => $smsEnabled && match ($smsProvider) {
                    'africas_talking' => $hasAfricaTalkingCredentials,
                    'twilio' => $hasTwilioCredentials,
                    default => false,
                },
                'section' => 'sms',
                'modal_id' => 'configureSmsApp',
                'meta' => ($settings['sms']['sender_id'] ?? '') ?: 'No sender ID',
                'url' => route('settings.index') . '#sms-settings',
            ],
        ];
    }

    private function cleanText(?string $value): string
    {
        return trim(strip_tags((string) $value));
    }

    private function storePresentSecrets(string $group, array $values): void
    {
        foreach (self::SECRET_KEYS[$group] ?? [] as $key) {
            if (array_key_exists($key, $values) && filled($values[$key])) {
                AppSetting::setValue($group, $key, $values[$key], true);
            }
        }
    }

    private function defaultSmtpHost(string $provider): string
    {
        return match ($provider) {
            'brevo' => 'smtp-relay.brevo.com',
            'resend' => 'smtp.resend.com',
            default => '',
        };
    }

    private function defaultSmtpUsername(string $provider): string
    {
        return match ($provider) {
            'resend' => 'resend',
            default => '',
        };
    }

    private function smsProviderLabel(string $provider): string
    {
        return match ($provider) {
            'africas_talking' => 'Africa\'s Talking',
            'twilio' => 'Twilio',
            default => 'Not selected',
        };
    }

    private function smsProviderImage(string $provider): ?string
    {
        return match ($provider) {
            'africas_talking' => '/images/apps/africas-talking.png',
            'twilio' => '/images/apps/twilio.svg',
            default => null,
        };
    }

    private function smsProviderWebsite(string $provider): string
    {
        return match ($provider) {
            'twilio' => 'twilio.com',
            'africas_talking' => 'africastalking.com',
            default => '',
        };
    }
}
