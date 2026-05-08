<?php

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the settings and manage apps pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('Payment Methods')
        ->assertSee('Email Delivery')
        ->assertSee('Brevo SMTP')
        ->assertSee('Resend')
        ->assertSee('Google Analytics')
        ->assertSee('africas_talking')
        ->assertSee('Twilio');

    $this->actingAs($user)
        ->get(route('settings.apps'))
        ->assertOk()
        ->assertSee('Manage Apps')
        ->assertSee('Payment Methods')
        ->assertSee('Email Delivery')
        ->assertSee('SMS Messaging');
});

it('saves payment email analytics and sms settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('settings.update', 'payments'), [
            'mpesa_enabled' => '1',
            'mpesa_type' => 'paybill',
            'mpesa_paybill_business_number' => '123456',
            'mpesa_paybill_account_number' => '{invoice_number}',
            'mpesa_paybill_account_name' => 'Velok',
            'bank_enabled' => '1',
            'bank_name' => 'Equity Bank',
            'bank_account_name' => 'Velok Holdings',
            'bank_account_number' => '0011223344',
            'bank_branch' => 'Nairobi CBD',
            'cash_enabled' => '1',
            'cash_instruction' => 'Confirm payment before dispatch.',
            'thank_you_message' => 'Thank you for choosing {company_name}.',
        ])
        ->assertRedirect(route('settings.index') . '#payments-settings');

    expect(AppSetting::bool('payments', 'mpesa_enabled'))->toBeTrue();
    expect(AppSetting::value('payments', 'mpesa_paybill_business_number'))->toBe('123456');
    expect(AppSetting::query()->where('group', 'payments')->where('key', 'mpesa_paybill_business_number')->value('value'))->not->toBe('123456');
    expect(AppSetting::value('invoice', 'thank_you_message'))->toBe('Thank you for choosing {company_name}.');

    $this->actingAs($user)
        ->patch(route('settings.update', 'email'), [
            'enabled' => '1',
            'provider' => 'resend',
            'from_name' => 'Velok',
            'from_address' => 'sales@example.com',
            'smtp_host' => 'smtp.resend.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_username' => 'resend',
            'smtp_password' => 'smtp-secret',
            'resend_api_key' => 're_secret',
        ])
        ->assertRedirect(route('settings.index') . '#email-settings');

    expect(AppSetting::bool('email', 'enabled'))->toBeTrue();
    expect(AppSetting::value('email', 'provider'))->toBe('resend');
    expect(AppSetting::value('email', 'resend_api_key'))->toBe('re_secret');
    expect(AppSetting::query()->where('group', 'email')->where('key', 'resend_api_key')->value('value'))->not->toBe('re_secret');

    $this->actingAs($user)
        ->patch(route('settings.update', 'analytics'), [
            'enabled' => '1',
            'property_id' => '123456789',
            'measurement_id' => 'G-ABC123',
            'credentials_path' => '/secure/ga.json',
            'credentials_json' => '{"client_email":"analytics@example.com"}',
        ])
        ->assertRedirect(route('settings.index') . '#analytics-settings');

    expect(AppSetting::bool('analytics', 'enabled'))->toBeTrue();
    expect(AppSetting::value('analytics', 'property_id'))->toBe('123456789');
    expect(AppSetting::value('analytics', 'credentials_json'))->toContain('analytics@example.com');

    $this->actingAs($user)
        ->patch(route('settings.update', 'sms'), [
            'enabled' => '1',
            'provider' => 'twilio',
            'sender_id' => 'VELOK',
            'default_country_code' => '+254',
            'twilio_account_sid' => 'AC123',
            'twilio_auth_token' => 'twilio-secret',
            'twilio_from' => '+15551234567',
        ])
        ->assertRedirect(route('settings.index') . '#sms-settings');

    expect(AppSetting::bool('sms', 'enabled'))->toBeTrue();
    expect(AppSetting::value('sms', 'provider'))->toBe('twilio');
    expect(AppSetting::value('sms', 'twilio_auth_token'))->toBe('twilio-secret');

    $this->actingAs($user)
        ->get(route('settings.apps'))
        ->assertOk()
        ->assertSee('Connected');
});
