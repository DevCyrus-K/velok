@extends('layouts.vertical', ['title' => 'Settings'])

@section('css')
<style>
    .settings-stat-icon,
    .settings-action-icon {
        width: 2.75rem;
        height: 2.75rem;
        flex: 0 0 2.75rem;
    }

    .settings-action-card {
        display: block;
        height: 100%;
        color: inherit;
        text-decoration: none;
    }

    .settings-action-card:hover {
        border-color: var(--bs-primary);
    }

    .settings-health-table {
        min-width: 760px;
    }
</style>
@endsection

@section('content')
@php
    $managedApps = collect($apps ?? []);
    $connectedManagedApps = $managedApps->where('connected', true)->count();
    $emailProviderLabels = [
        'brevo' => 'Brevo SMTP',
        'resend' => 'Resend',
        'smtp' => 'Custom SMTP',
        'log' => 'Log only',
    ];
    $smsProviderLabels = [
        'africas_talking' => 'Africa\'s Talking',
        'twilio' => 'Twilio',
        'none' => 'None',
    ];
    $settingsStats = [
        [
            'label' => 'Managed Apps',
            'value' => $connectedManagedApps . '/' . $managedApps->count(),
            'note' => 'Connected configs',
            'icon' => 'layout-grid',
        ],
        [
            'label' => 'Email Delivery',
            'value' => $emailProviderLabels[$settings['email']['provider'] ?? 'brevo'] ?? 'Email',
            'note' => (($settings['email']['enabled'] ?? '0') === '1') ? 'Sending enabled' : 'Sending paused',
            'icon' => 'mail-check',
        ],
        [
            'label' => 'SMS Messaging',
            'value' => $smsProviderLabels[$settings['sms']['provider'] ?? 'africas_talking'] ?? 'SMS',
            'note' => (($settings['sms']['enabled'] ?? '0') === '1') ? 'Alerts enabled' : 'Alerts paused',
            'icon' => 'message-circle',
        ],
        [
            'label' => 'Google Analytics',
            'value' => ($settings['analytics']['property_id'] ?? '') ?: 'Not set',
            'note' => (($settings['analytics']['enabled'] ?? '0') === '1') ? 'Reports connected' : 'Reports waiting',
            'icon' => 'chart-no-axes-combined',
        ],
    ];
    $workspaceActions = [
        [
            'title' => 'Manage Apps',
            'description' => 'Configure Payment Methods, Email Delivery, Resend, Custom SMTP, Google Analytics, and SMS.',
            'icon' => 'plug-zap',
            'url' => route('settings.apps'),
            'badge' => 'Configure',
        ],
        [
            'title' => 'Reports',
            'description' => 'Review live business performance, lead quality, revenue, and visitor insight.',
            'icon' => 'chart-column',
            'url' => route('second', ['reports', 'overview']),
            'badge' => 'Analyze',
        ],
        [
            'title' => 'Todo',
            'description' => 'Keep internal follow-up tasks moving after leads, invoices, and messages arrive.',
            'icon' => 'clipboard-check',
            'url' => route('todo.index'),
            'badge' => 'Work',
        ],
        [
            'title' => 'My Account',
            'description' => 'Update profile details, password, and account identity.',
            'icon' => 'circle-user',
            'url' => route('account.show'),
            'badge' => 'Profile',
        ],
    ];
@endphp

<div class="row mb-3">
    <div class="col-lg-8">
        <h4 class="fw-semibold mb-1">Settings</h4>
        <p class="text-muted mb-0">Workspace health, account actions, and operational shortcuts live here. App credentials are configured in Manage Apps.</p>
    </div>
    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a class="btn btn-primary" href="{{ route('settings.apps') }}">
            <i class="icon-sm me-1" data-lucide="sliders-horizontal"></i>Configure Apps
        </a>
    </div>
</div>

<div class="row g-3">
    @foreach($settingsStats as $stat)
    <div class="col-xl-3 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="settings-stat-icon rounded bg-light d-flex align-items-center justify-content-center text-primary">
                        <i data-lucide="{{ $stat['icon'] }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="card-title mb-1">{{ $stat['label'] }}</p>
                        <h5 class="fw-semibold mb-1 text-truncate">{{ $stat['value'] }}</h5>
                        <p class="text-muted mb-0 small">{{ $stat['note'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card-title mb-1">Configuration Health</h4>
                    <p class="text-muted mb-0">Current app readiness across the workspace.</p>
                </div>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('settings.apps') }}">Manage Apps</a>
            </div>
            <div class="table-responsive">
                <table class="table table-centered settings-health-table mb-0">
                    <thead class="text-uppercase fs-12">
                        <tr>
                            <th>App</th>
                            <th>Domain</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managedApps as $app)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-sm rounded bg-light d-flex align-items-center justify-content-center">
                                        @if(!empty($app['image']))
                                            <img alt="{{ $app['domain'] }} logo" class="avatar-xs" src="{{ $app['image'] }}" loading="lazy" decoding="async" />
                                        @else
                                            <i data-lucide="{{ $app['icon'] }}" class="icon-sm text-primary"></i>
                                        @endif
                                    </span>
                                    <span class="fw-semibold">{{ $app['name'] }}</span>
                                </div>
                            </td>
                            <td>{{ $app['domain'] }}</td>
                            <td>
                                <span class="badge {{ $app['status_class'] ?? ($app['connected'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning') }}">
                                    {{ $app['status_label'] ?? ($app['connected'] ? 'Connected' : 'Needs Setup') }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $app['meta'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Workspace Actions</h4>
                <p class="text-muted mb-0">Fast routes for daily admin work.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($workspaceActions as $action)
                    <div class="col-12">
                        <a class="settings-action-card border rounded p-3" href="{{ $action['url'] }}">
                            <span class="d-flex align-items-start gap-3">
                                <span class="settings-action-icon rounded bg-light d-flex align-items-center justify-content-center text-primary">
                                    <i data-lucide="{{ $action['icon'] }}"></i>
                                </span>
                                <span class="d-block min-w-0">
                                    <span class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="fw-semibold text-dark">{{ $action['title'] }}</span>
                                        <span class="badge badge-soft-primary">{{ $action['badge'] }}</span>
                                    </span>
                                    <span class="text-muted small d-block">{{ $action['description'] }}</span>
                                </span>
                            </span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1" id="company-settings">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-1">Company Settings</h4>
                <p class="text-muted mb-0">Keep your brand and contact details consistent across PDFs, emails, and customer-facing pages.</p>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update', 'company') }}" method="POST" class="row g-3" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-6">
                        <label class="form-label" for="companyName">Company Name</label>
                        <input id="companyName" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $settings['company']['name'] ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyEmail">Company Email</label>
                        <input id="companyEmail" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $settings['company']['email'] ?? '') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyPhone">Company Phone</label>
                        <input id="companyPhone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $settings['company']['phone'] ?? '') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyAddress1">Address Line 1</label>
                        <input id="companyAddress1" name="address_line_1" type="text" class="form-control @error('address_line_1') is-invalid @enderror" value="{{ old('address_line_1', $settings['company']['address_line_1'] ?? '') }}">
                        @error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="companyAddress2">Address Line 2</label>
                        <input id="companyAddress2" name="address_line_2" type="text" class="form-control @error('address_line_2') is-invalid @enderror" value="{{ old('address_line_2', $settings['company']['address_line_2'] ?? '') }}">
                        @error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyWebsite">Company Website</label>
                        <input id="companyWebsite" name="website" type="url" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $settings['company']['website'] ?? '') }}">
                        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyRegistration">Business Registration Number</label>
                        <input id="companyRegistration" name="business_registration_number" type="text" class="form-control @error('business_registration_number') is-invalid @enderror" value="{{ old('business_registration_number', $settings['company']['business_registration_number'] ?? '') }}">
                        @error('business_registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyRepresentative">Authorized Representative</label>
                        <input id="companyRepresentative" name="authorized_representative_name" type="text" class="form-control @error('authorized_representative_name') is-invalid @enderror" value="{{ old('authorized_representative_name', $settings['company']['authorized_representative_name'] ?? '') }}">
                        @error('authorized_representative_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyRepresentativeTitle">Representative Title</label>
                        <input id="companyRepresentativeTitle" name="authorized_representative_title" type="text" class="form-control @error('authorized_representative_title') is-invalid @enderror" value="{{ old('authorized_representative_title', $settings['company']['authorized_representative_title'] ?? '') }}">
                        @error('authorized_representative_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyLiabilityCap">Liability Cap Amount</label>
                        <input id="companyLiabilityCap" name="liability_cap_amount" type="text" class="form-control @error('liability_cap_amount') is-invalid @enderror" value="{{ old('liability_cap_amount', $settings['company']['liability_cap_amount'] ?? '') }}">
                        @error('liability_cap_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="companyLogo">Company Logo</label>
                        <input id="companyLogo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" class="form-control @error('logo') is-invalid @enderror">
                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        @if(!empty($settings['company']['logo_path']))
                            <img src="{{ app(\App\Support\CompanyProfile::class)->logoUrl() }}" alt="Company logo preview" style="height:40px; width:auto; object-fit:contain;">
                        @endif
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="icon-sm me-1"></i>Save Company Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
