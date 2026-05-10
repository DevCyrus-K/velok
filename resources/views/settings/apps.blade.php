@extends('layouts.vertical', ['title' => 'Manage Apps'])

@section('css')
<style>
    .app-logo-box {
        width: 3rem;
        height: 3rem;
        flex: 0 0 3rem;
    }

    .app-logo-img {
        max-width: 2.25rem;
        max-height: 2.25rem;
        object-fit: contain;
    }

    .managed-app-card .card-body {
        min-height: 260px;
    }

    .managed-app-title,
    .managed-app-meta {
        min-width: 0;
    }

    .managed-app-title h4,
    .managed-app-domain {
        overflow-wrap: anywhere;
    }

    .managed-app-provider {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        margin-top: .2rem;
    }

    .managed-app-modal-icon {
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
    }

    .managed-app-form-note {
        border-left: 3px solid var(--bs-primary);
    }
</style>
@endsection

@section('content')
@php
    $connectedApps = collect($apps)->where('connected', true)->count();
@endphp

<div class="row mb-3" id="managed-apps">
    <div class="col-lg-8">
        <h4 class="fw-semibold mb-2">Manage Apps</h4>
        <p class="text-muted mb-0">{{ $connectedApps }}/{{ count($apps) }} app configs are connected. Configure each integration without leaving this page.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a class="btn btn-outline-primary" href="{{ route('settings.index') }}">
            <i class="icon-sm me-1" data-lucide="settings"></i>Settings
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <p class="fw-semibold mb-1">Check the app configuration and try again.</p>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 mt-2">
    @foreach($apps as $app)
        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 managed-app-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <div class="app-logo-box d-flex me-2 bg-light align-items-center justify-content-center rounded">
                            @if(!empty($app['image']))
                                <img alt="{{ $app['domain'] }} logo" class="app-logo-img" src="{{ $app['image'] }}" loading="lazy" decoding="async" />
                            @else
                                <i class="text-primary" data-lucide="{{ $app['icon'] }}"></i>
                            @endif
                        </div>
                        <div class="form-check form-switch checkbox-xl mb-0">
                            <input class="form-check-input" id="appSwitch{{ $loop->index }}" role="switch" type="checkbox" @checked($app['connected']) disabled aria-label="{{ $app['name'] }} connection status" />
                        </div>
                    </div>
                    <div class="managed-app-title mb-2">
                        <h4 class="fw-semibold mb-0">{{ $app['name'] }}</h4>
                        @if(!empty($app['website']))
                            <a class="managed-app-provider link-warning fs-13 fw-normal" href="https://{{ $app['website'] }}" target="_blank" rel="noopener">
                                <i class="icon-sm" data-lucide="external-link"></i>{{ $app['website'] }}
                            </a>
                        @endif
                    </div>
                    <p class="mb-0">{{ $app['description'] }}</p>
                    <div class="d-flex align-items-center justify-content-between gap-3 mt-3">
                        <div class="managed-app-meta">
                            <p class="managed-app-domain mb-0 fw-semibold text-dark">{{ $app['domain'] }}</p>
                            <p class="text-muted mb-0">{{ $app['meta'] }}</p>
                        </div>
                        <div>
                            <span class="badge {{ $app['status_class'] ?? ($app['connected'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') }} fs-12 py-1 px-2">
                                {{ $app['status_label'] ?? ($app['connected'] ? 'Connected' : 'Needs Setup') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-primary btn-sm fw-semibold" type="button" data-bs-toggle="modal" data-bs-target="#{{ $app['modal_id'] }}">
                        <i class="icon-sm me-1" data-lucide="sliders-horizontal"></i>Configure
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

@foreach($apps as $app)
    @php
        $modalId = $app['modal_id'];
        $formId = $modalId . 'Form';
        $section = $app['section'];
        $provider = $app['provider'] ?? null;
        $currentEmailProvider = $settings['email']['provider'] ?? 'brevo';
        $emailDefaultHost = match ($provider) {
            'brevo' => 'smtp-relay.brevo.com',
            'resend' => 'smtp.resend.com',
            default => '',
        };
        $emailDefaultUsername = $provider === 'resend' ? 'resend' : '';
        $emailSmtpHost = old('smtp_host', $currentEmailProvider === $provider ? ($settings['email']['smtp_host'] ?? $emailDefaultHost) : $emailDefaultHost);
        $emailSmtpUsername = old('smtp_username', $currentEmailProvider === $provider ? ($settings['email']['smtp_username'] ?? $emailDefaultUsername) : $emailDefaultUsername);
    @endphp
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <span class="managed-app-modal-icon rounded bg-light d-flex align-items-center justify-content-center">
                            @if(!empty($app['image']))
                                <img alt="{{ $app['domain'] }} logo" class="app-logo-img" src="{{ $app['image'] }}" loading="lazy" decoding="async" />
                            @else
                                <i class="text-primary" data-lucide="{{ $app['icon'] }}"></i>
                            @endif
                        </span>
                        <div>
                            <h5 class="modal-title mb-1" id="{{ $modalId }}Label">{{ $app['name'] }}</h5>
                            <p class="text-muted mb-0">{{ $app['domain'] }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="{{ $formId }}" action="{{ $section === 'payments' ? route('settings.payment.update') : route('settings.update', $section) }}" method="POST">
                        @csrf
                        @if($section !== 'payments')
                            @method('PATCH')
                        @endif
                        <input type="hidden" name="manage_apps" value="1">
                        <input type="hidden" name="active_modal" value="{{ $modalId }}">

                        @if($section === 'payments')
                            @php
                                $paymentSettings = $settings['payments'] ?? [];
                                $invoiceSettings = $settings['invoice'] ?? [];
                                $paymentSecrets = $secretStatus['payments'] ?? [];
                                $mpesaType = old('mpesa_type', $paymentSettings['mpesa_type'] ?? 'till');
                            @endphp
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" id="{{ $modalId }}MpesaEnabled" name="mpesa_enabled" type="checkbox" value="1" @checked(($settings['payments']['mpesa_enabled'] ?? '0') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}MpesaEnabled">M-Pesa enabled</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-block">M-Pesa payment type</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" id="{{ $modalId }}MpesaTypeTill" name="mpesa_type" type="radio" value="till" @checked($mpesaType === 'till')>
                                            <label class="form-check-label" for="{{ $modalId }}MpesaTypeTill">Till Number</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" id="{{ $modalId }}MpesaTypePaybill" name="mpesa_type" type="radio" value="paybill" @checked($mpesaType === 'paybill')>
                                            <label class="form-check-label" for="{{ $modalId }}MpesaTypePaybill">Paybill</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" id="{{ $modalId }}MpesaTypePochi" name="mpesa_type" type="radio" value="pochi" @checked($mpesaType === 'pochi')>
                                            <label class="form-check-label" for="{{ $modalId }}MpesaTypePochi">Pochi la Biashara</label>
                                        </div>
                                    </div>
                                    @error('mpesa_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaTillNumber">Till Number</label>
                                    <input autocomplete="new-password" class="form-control @error('mpesa_till_number') is-invalid @enderror" id="{{ $modalId }}MpesaTillNumber" name="mpesa_till_number" placeholder="{{ ($paymentSecrets['mpesa_till_number'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="text" inputmode="numeric" data-mpesa-type="till">
                                    @error('mpesa_till_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaTillAccountName">Till Account Name</label>
                                    <input class="form-control @error('mpesa_till_account_name') is-invalid @enderror" id="{{ $modalId }}MpesaTillAccountName" name="mpesa_till_account_name" type="text" value="{{ old('mpesa_till_account_name', $paymentSettings['mpesa_till_account_name'] ?? '') }}" data-mpesa-type="till">
                                    @error('mpesa_till_account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaBusinessNumber">Paybill Business Number</label>
                                    <input autocomplete="new-password" class="form-control @error('mpesa_paybill_business_number') is-invalid @enderror" id="{{ $modalId }}MpesaBusinessNumber" name="mpesa_paybill_business_number" placeholder="{{ ($paymentSecrets['mpesa_paybill_business_number'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="text" inputmode="numeric" data-mpesa-type="paybill">
                                    @error('mpesa_paybill_business_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaPaybillAccountNumber">Paybill Account Number</label>
                                    <input autocomplete="new-password" class="form-control @error('mpesa_paybill_account_number') is-invalid @enderror" id="{{ $modalId }}MpesaPaybillAccountNumber" name="mpesa_paybill_account_number" placeholder="{{ ($paymentSecrets['mpesa_paybill_account_number'] ?? false) ? 'Saved - leave blank to keep. You can use {invoice_number}.' : 'Example: {invoice_number}' }}" type="text" data-mpesa-type="paybill">
                                    @error('mpesa_paybill_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaPaybillAccountName">Paybill Account Name</label>
                                    <input class="form-control @error('mpesa_paybill_account_name') is-invalid @enderror" id="{{ $modalId }}MpesaPaybillAccountName" name="mpesa_paybill_account_name" type="text" value="{{ old('mpesa_paybill_account_name', $paymentSettings['mpesa_paybill_account_name'] ?? '') }}" data-mpesa-type="paybill">
                                    @error('mpesa_paybill_account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaPochiPhone">Pochi Phone Number</label>
                                    <input autocomplete="new-password" class="form-control @error('mpesa_pochi_phone') is-invalid @enderror" id="{{ $modalId }}MpesaPochiPhone" name="mpesa_pochi_phone" placeholder="{{ ($paymentSecrets['mpesa_pochi_phone'] ?? false) ? 'Saved - leave blank to keep' : '07XXXXXXXX or 01XXXXXXXX' }}" type="text" inputmode="numeric" data-mpesa-type="pochi">
                                    @error('mpesa_pochi_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}MpesaPochiName">Pochi Registered Name</label>
                                    <input class="form-control @error('mpesa_pochi_registered_name') is-invalid @enderror" id="{{ $modalId }}MpesaPochiName" name="mpesa_pochi_registered_name" type="text" value="{{ old('mpesa_pochi_registered_name', $paymentSettings['mpesa_pochi_registered_name'] ?? '') }}" data-mpesa-type="pochi">
                                    @error('mpesa_pochi_registered_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12"><hr></div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" id="{{ $modalId }}CashEnabled" name="cash_enabled" type="checkbox" value="1" @checked(($paymentSettings['cash_enabled'] ?? '1') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}CashEnabled">Cash enabled</label>
                                    </div>
                                    <label class="form-label" for="{{ $modalId }}CashInstruction">Cash Instruction</label>
                                    <textarea class="form-control bg-light-subtle @error('cash_instruction') is-invalid @enderror" id="{{ $modalId }}CashInstruction" name="cash_instruction" rows="3">{{ old('cash_instruction', $paymentSettings['cash_instruction'] ?? '') }}</textarea>
                                    @error('cash_instruction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" id="{{ $modalId }}BankEnabled" name="bank_enabled" type="checkbox" value="1" @checked(($paymentSettings['bank_enabled'] ?? '0') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}BankEnabled">Bank transfer enabled</label>
                                    </div>
                                    <label class="form-label" for="{{ $modalId }}BankName">Bank Name</label>
                                    <input class="form-control @error('bank_name') is-invalid @enderror" id="{{ $modalId }}BankName" name="bank_name" type="text" value="{{ old('bank_name', $paymentSettings['bank_name'] ?? '') }}">
                                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}BankAccountName">Bank Account Name</label>
                                    <input class="form-control @error('bank_account_name') is-invalid @enderror" id="{{ $modalId }}BankAccountName" name="bank_account_name" type="text" value="{{ old('bank_account_name', $paymentSettings['bank_account_name'] ?? '') }}">
                                    @error('bank_account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}BankAccountNumber">Bank Account Number</label>
                                    <input autocomplete="new-password" class="form-control @error('bank_account_number') is-invalid @enderror" id="{{ $modalId }}BankAccountNumber" name="bank_account_number" placeholder="{{ ($paymentSecrets['bank_account_number'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="text" inputmode="numeric">
                                    @error('bank_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}BankBranch">Branch</label>
                                    <input class="form-control @error('bank_branch') is-invalid @enderror" id="{{ $modalId }}BankBranch" name="bank_branch" type="text" value="{{ old('bank_branch', $paymentSettings['bank_branch'] ?? '') }}">
                                    @error('bank_branch')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="{{ $modalId }}BankSwiftCode">Swift Code</label>
                                    <input class="form-control @error('bank_swift_code') is-invalid @enderror" id="{{ $modalId }}BankSwiftCode" name="bank_swift_code" type="text" value="{{ old('bank_swift_code', $paymentSettings['bank_swift_code'] ?? '') }}">
                                    @error('bank_swift_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12"><hr></div>

                                <div class="col-12">
                                    <label class="form-label" for="{{ $modalId }}ThankYouMessage">Invoice Thank You Message</label>
                                    <textarea class="form-control bg-light-subtle @error('thank_you_message') is-invalid @enderror" id="{{ $modalId }}ThankYouMessage" name="thank_you_message" rows="4">{{ old('thank_you_message', $invoiceSettings['thank_you_message'] ?? \App\Support\CompanyProfile::DEFAULT_THANK_YOU_TEMPLATE) }}</textarea>
                                    @error('thank_you_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">Supported placeholders: {company_name}, {company_email}, {company_phone}</div>
                                </div>
                                <div class="col-12">
                                    <div class="managed-app-form-note bg-light-subtle p-3">
                                        <p class="mb-0 text-muted">Only enabled methods appear on invoices and PDFs. Saved account numbers stay encrypted and are not printed back into this settings form.</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($section === 'email')
                            <input type="hidden" name="provider" value="{{ $provider }}">
                            @if($provider === 'resend')
                                <input type="hidden" name="smtp_host" value="smtp.resend.com">
                                <input type="hidden" name="smtp_port" value="587">
                                <input type="hidden" name="smtp_encryption" value="tls">
                                <input type="hidden" name="smtp_username" value="resend">
                            @endif
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-md-4">
                                        <input class="form-check-input" id="{{ $modalId }}EmailEnabled" name="enabled" type="checkbox" value="1" @checked(($settings['email']['enabled'] ?? '0') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}EmailEnabled">Email sending enabled</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}FromName">From Name</label>
                                    <input class="form-control @error('from_name') is-invalid @enderror" id="{{ $modalId }}FromName" name="from_name" type="text" value="{{ old('from_name', $settings['email']['from_name'] ?? '') }}">
                                    @error('from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}FromAddress">From Email</label>
                                    <input class="form-control @error('from_address') is-invalid @enderror" id="{{ $modalId }}FromAddress" name="from_address" type="email" value="{{ old('from_address', $settings['email']['from_address'] ?? '') }}" required>
                                    @error('from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <hr class="my-1">
                                </div>

                                <div class="col-12">
                                    <h6 class="fw-semibold mb-1">Sender Addresses</h6>
                                    <p class="text-muted mb-0 small">Messages use Info, OTPs and login alerts use No Reply, and invoices/quotations use Sales.</p>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}MessagesFromAddress">Info Email</label>
                                    <input class="form-control @error('mail_from_messages_address') is-invalid @enderror" id="{{ $modalId }}MessagesFromAddress" name="mail_from_messages_address" type="email" value="{{ old('mail_from_messages_address', $settings['email']['mail_from_messages_address'] ?? 'info@kwikshiftmovers.co.ke') }}" required>
                                    @error('mail_from_messages_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}NoReplyFromAddress">No Reply Email</label>
                                    <input class="form-control @error('mail_from_noreply_address') is-invalid @enderror" id="{{ $modalId }}NoReplyFromAddress" name="mail_from_noreply_address" type="email" value="{{ old('mail_from_noreply_address', $settings['email']['mail_from_noreply_address'] ?? 'noreply@kwikshiftmovers.co.ke') }}" required>
                                    @error('mail_from_noreply_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}SalesFromAddress">Sales Email</label>
                                    <input class="form-control @error('mail_from_invoices_address') is-invalid @enderror" id="{{ $modalId }}SalesFromAddress" name="mail_from_invoices_address" type="email" value="{{ old('mail_from_invoices_address', $settings['email']['mail_from_invoices_address'] ?? 'sales@kwikshiftmovers.co.ke') }}" required>
                                    @error('mail_from_invoices_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}MessagesFromName">Info Name</label>
                                    <input class="form-control @error('mail_from_messages_name') is-invalid @enderror" id="{{ $modalId }}MessagesFromName" name="mail_from_messages_name" type="text" value="{{ old('mail_from_messages_name', $settings['email']['mail_from_messages_name'] ?? '') }}">
                                    @error('mail_from_messages_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}NoReplyFromName">No Reply Name</label>
                                    <input class="form-control @error('mail_from_noreply_name') is-invalid @enderror" id="{{ $modalId }}NoReplyFromName" name="mail_from_noreply_name" type="text" value="{{ old('mail_from_noreply_name', $settings['email']['mail_from_noreply_name'] ?? '') }}">
                                    @error('mail_from_noreply_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}SalesFromName">Sales Name</label>
                                    <input class="form-control @error('mail_from_invoices_name') is-invalid @enderror" id="{{ $modalId }}SalesFromName" name="mail_from_invoices_name" type="text" value="{{ old('mail_from_invoices_name', $settings['email']['mail_from_invoices_name'] ?? '') }}">
                                    @error('mail_from_invoices_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                @if($provider === 'resend')
                                    <div class="col-md-12">
                                        <label class="form-label" for="{{ $modalId }}ResendApiKey">Resend API Key</label>
                                        <input autocomplete="new-password" class="form-control @error('resend_api_key') is-invalid @enderror" id="{{ $modalId }}ResendApiKey" name="resend_api_key" placeholder="{{ ($secretStatus['email']['resend_api_key'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="password">
                                        @error('resend_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label" for="{{ $modalId }}SmtpHost">SMTP Host</label>
                                        <input class="form-control @error('smtp_host') is-invalid @enderror" id="{{ $modalId }}SmtpHost" name="smtp_host" placeholder="{{ $provider === 'brevo' ? 'smtp-relay.brevo.com' : 'smtp.example.com' }}" type="text" value="{{ $emailSmtpHost }}">
                                        @error('smtp_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="{{ $modalId }}SmtpPort">Port</label>
                                        <input class="form-control @error('smtp_port') is-invalid @enderror" id="{{ $modalId }}SmtpPort" name="smtp_port" type="number" value="{{ old('smtp_port', $settings['email']['smtp_port'] ?? '587') }}">
                                        @error('smtp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="{{ $modalId }}SmtpEncryption">Security</label>
                                        <select class="form-select @error('smtp_encryption') is-invalid @enderror" id="{{ $modalId }}SmtpEncryption" name="smtp_encryption">
                                            <option value="tls" @selected(old('smtp_encryption', $settings['email']['smtp_encryption'] ?? 'tls') === 'tls')>TLS</option>
                                            <option value="ssl" @selected(old('smtp_encryption', $settings['email']['smtp_encryption'] ?? 'tls') === 'ssl')>SSL</option>
                                            <option value="" @selected(old('smtp_encryption', $settings['email']['smtp_encryption'] ?? 'tls') === '')>None</option>
                                        </select>
                                        @error('smtp_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="{{ $modalId }}SmtpUsername">SMTP Username</label>
                                        <input class="form-control @error('smtp_username') is-invalid @enderror" id="{{ $modalId }}SmtpUsername" name="smtp_username" type="text" value="{{ $emailSmtpUsername }}">
                                        @error('smtp_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="{{ $modalId }}SmtpPassword">SMTP Password</label>
                                        <input autocomplete="new-password" class="form-control @error('smtp_password') is-invalid @enderror" id="{{ $modalId }}SmtpPassword" name="smtp_password" placeholder="{{ ($secretStatus['email']['smtp_password'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="password">
                                        @error('smtp_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    @if($provider === 'brevo')
                                        <div class="col-md-12">
                                            <label class="form-label" for="{{ $modalId }}BrevoApiKey">Brevo API Key</label>
                                            <input autocomplete="new-password" class="form-control @error('brevo_api_key') is-invalid @enderror" id="{{ $modalId }}BrevoApiKey" name="brevo_api_key" placeholder="{{ ($secretStatus['email']['brevo_api_key'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="password">
                                            @error('brevo_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @elseif($section === 'analytics')
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-md-4">
                                        <input class="form-check-input" id="{{ $modalId }}AnalyticsEnabled" name="enabled" type="checkbox" value="1" @checked(($settings['analytics']['enabled'] ?? '0') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}AnalyticsEnabled">Analytics enabled</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}PropertyId">GA4 Property ID</label>
                                    <input class="form-control @error('property_id') is-invalid @enderror" id="{{ $modalId }}PropertyId" name="property_id" placeholder="123456789" type="text" value="{{ old('property_id', $settings['analytics']['property_id'] ?? '') }}">
                                    @error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}MeasurementId">Measurement ID</label>
                                    <input class="form-control @error('measurement_id') is-invalid @enderror" id="{{ $modalId }}MeasurementId" name="measurement_id" placeholder="G-XXXXXXXXXX" type="text" value="{{ old('measurement_id', $settings['analytics']['measurement_id'] ?? '') }}">
                                    @error('measurement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="{{ $modalId }}CredentialsPath">Credentials Path</label>
                                    <input class="form-control @error('credentials_path') is-invalid @enderror" id="{{ $modalId }}CredentialsPath" name="credentials_path" type="text" value="{{ old('credentials_path', $settings['analytics']['credentials_path'] ?? '') }}">
                                    @error('credentials_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="{{ $modalId }}CredentialsJson">Service Account JSON</label>
                                    <textarea class="form-control bg-light-subtle @error('credentials_json') is-invalid @enderror" id="{{ $modalId }}CredentialsJson" name="credentials_json" placeholder="{{ ($secretStatus['analytics']['credentials_json'] ?? false) ? 'Saved - leave blank to keep' : 'Paste JSON only if you do not use a file path' }}" rows="5"></textarea>
                                    @error('credentials_json')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @elseif($section === 'sms')
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch pt-md-4">
                                        <input class="form-check-input" id="{{ $modalId }}SmsEnabled" name="enabled" type="checkbox" value="1" @checked(($settings['sms']['enabled'] ?? '0') === '1')>
                                        <label class="form-check-label" for="{{ $modalId }}SmsEnabled">SMS alerts enabled</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}SmsProvider">Provider</label>
                                    <select class="form-select @error('provider') is-invalid @enderror" id="{{ $modalId }}SmsProvider" name="provider">
                                        <option value="africas_talking" @selected(old('provider', $settings['sms']['provider'] ?? 'africas_talking') === 'africas_talking')>Africa's Talking</option>
                                        <option value="twilio" @selected(old('provider', $settings['sms']['provider'] ?? 'africas_talking') === 'twilio')>Twilio</option>
                                        <option value="none" @selected(old('provider', $settings['sms']['provider'] ?? 'africas_talking') === 'none')>None</option>
                                    </select>
                                    @error('provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}SenderId">Sender ID</label>
                                    <input class="form-control @error('sender_id') is-invalid @enderror" id="{{ $modalId }}SenderId" name="sender_id" type="text" value="{{ old('sender_id', $settings['sms']['sender_id'] ?? '') }}">
                                    @error('sender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}CountryCode">Default Country Code</label>
                                    <input class="form-control @error('default_country_code') is-invalid @enderror" id="{{ $modalId }}CountryCode" name="default_country_code" type="text" value="{{ old('default_country_code', $settings['sms']['default_country_code'] ?? '+254') }}">
                                    @error('default_country_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}AfricaUsername">Africa's Talking Username</label>
                                    <input class="form-control @error('africas_talking_username') is-invalid @enderror" id="{{ $modalId }}AfricaUsername" name="africas_talking_username" type="text" value="{{ old('africas_talking_username', $settings['sms']['africas_talking_username'] ?? '') }}">
                                    @error('africas_talking_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}AfricaApiKey">Africa's Talking API Key</label>
                                    <input autocomplete="new-password" class="form-control @error('africas_talking_api_key') is-invalid @enderror" id="{{ $modalId }}AfricaApiKey" name="africas_talking_api_key" placeholder="{{ ($secretStatus['sms']['africas_talking_api_key'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="password">
                                    @error('africas_talking_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}TwilioSid">Twilio Account SID</label>
                                    <input class="form-control @error('twilio_account_sid') is-invalid @enderror" id="{{ $modalId }}TwilioSid" name="twilio_account_sid" type="text" value="{{ old('twilio_account_sid', $settings['sms']['twilio_account_sid'] ?? '') }}">
                                    @error('twilio_account_sid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}TwilioToken">Twilio Auth Token</label>
                                    <input autocomplete="new-password" class="form-control @error('twilio_auth_token') is-invalid @enderror" id="{{ $modalId }}TwilioToken" name="twilio_auth_token" placeholder="{{ ($secretStatus['sms']['twilio_auth_token'] ?? false) ? 'Saved - leave blank to keep' : '' }}" type="password">
                                    @error('twilio_auth_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $modalId }}TwilioFrom">Twilio From Number</label>
                                    <input class="form-control @error('twilio_from') is-invalid @enderror" id="{{ $modalId }}TwilioFrom" name="twilio_from" type="text" value="{{ old('twilio_from', $settings['sms']['twilio_from'] ?? '') }}">
                                    @error('twilio_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" form="{{ $formId }}">
                        <i class="icon-sm me-1" data-lucide="save"></i>Save Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Only show modal if there are validation errors
        const hasErrors = @json($errors->any());
        const activeModal = hasErrors ? @json(old('active_modal')) : null;

        if (activeModal && window.bootstrap) {
            const modalElement = document.getElementById(activeModal);

            if (modalElement) {
                new bootstrap.Modal(modalElement).show();
            }
        }

        // M-Pesa payment type toggle
        const mpesaRadios = document.querySelectorAll('input[name="mpesa_type"]');
        
        function updateMpesaFieldVisibility() {
            const selectedType = document.querySelector('input[name="mpesa_type"]:checked')?.value;
            
            // Hide/show fields based on selected type
            document.querySelectorAll('[data-mpesa-type]').forEach(field => {
                const fieldType = field.getAttribute('data-mpesa-type');
                const parentCol = field.closest('.col-md-6');
                
                if (parentCol) {
                    if (fieldType === selectedType) {
                        parentCol.style.display = 'block';
                    } else {
                        parentCol.style.display = 'none';
                    }
                }
            });
        }

        // Add change listeners to all M-Pesa radio buttons
        mpesaRadios.forEach(radio => {
            radio.addEventListener('change', updateMpesaFieldVisibility);
        });

        // Initialize visibility on page load
        updateMpesaFieldVisibility();
    });
</script>
@endsection
