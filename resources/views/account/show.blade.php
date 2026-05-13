@extends('layouts.vertical', ['title' => 'My Account'])

@section('css')
<style>
    .account-avatar {
        width: 92px;
        height: 92px;
    }
    .account-avatar-image {
        object-fit: cover;
    }
    .account-avatar-initials {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
        text-transform: uppercase;
    }
    .account-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .account-summary-item {
        border-bottom: 1px solid var(--bs-border-color);
        padding-bottom: 14px;
        margin-bottom: 14px;
    }
    .account-summary-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    .signature-preview {
        max-width: 260px;
        max-height: 96px;
        object-fit: contain;
    }
    .signature-pad {
        border: 1px dashed var(--bs-border-color);
        border-radius: 8px;
        cursor: crosshair;
        display: block;
        height: 180px;
        touch-action: none;
        width: 100%;
    }
    @media (max-width: 575.98px) {
        .account-avatar {
            width: 78px;
            height: 78px;
        }
        .account-avatar-initials {
            font-size: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
@php
    $profileFields = ['name', 'email', 'phone', 'job_title', 'avatar', 'signature_upload', 'signature_data'];
    $securityFields = ['current_password', 'password', 'password_confirmation', 'otp'];
    $profileHasErrors = collect($profileFields)->contains(fn ($field) => $errors->has($field));
    $securityHasErrors = collect($securityFields)->contains(fn ($field) => $errors->has($field));
    $activeTab = session('account_tab') ?? ($securityHasErrors ? 'security' : 'profile');
    $hasSignature = filled($signatureUrl ?? null);
    $signatureEditorOpen = ! $hasSignature || $errors->has('signature_upload') || $errors->has('signature_data');
    $emptySignaturePreview = 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';
    $twoFactorPending = session('two_factor_setup_pending') || $errors->has('otp');
    $lastLoginText = $user->last_login_at
        ? 'Last login: ' . $user->last_login_at->format('d M Y \a\t H:i') . ' from ' . ($user->last_login_ip ?: 'Unknown IP')
        : 'Last login: Not recorded yet';
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if($hasAvatar)
                <img alt="{{ $user->name }}" class="account-avatar account-avatar-image rounded-circle border bg-light" src="{{ $avatarUrl }}">
            @else
                <span aria-label="{{ $user->name }} initials" class="account-avatar account-avatar-initials rounded-circle border bg-primary-subtle text-primary">{{ $avatarInitials }}</span>
            @endif
            <div>
                <h4 class="card-title mb-1">My Account</h4>
                <p class="text-muted mb-0">{{ $user->name }} | {{ $user->email }}</p>
                <p class="text-muted mb-0 small">{{ $lastLoginText }}</p>
            </div>
        </div>
        @if($user->email_verified_at)
            <span class="badge badge-soft-success"><i class="icon-xs me-1" data-lucide="badge-check"></i>Verified Account</span>
        @else
            <span class="badge badge-soft-warning"><i class="icon-xs me-1" data-lucide="mail-warning"></i>Email Pending</span>
        @endif
    </div>
    <div class="card-body pb-0">
        <ul class="nav nav-tabs account-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}" class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" data-bs-toggle="tab" href="#account-profile" role="tab">
                    <i class="icon-sm" data-lucide="user-round"></i>Account Information
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}" class="nav-link {{ $activeTab === 'security' ? 'active' : '' }}" data-bs-toggle="tab" href="#account-security" role="tab">
                    <i class="icon-sm" data-lucide="shield-check"></i>Security
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content text-muted">
    <div class="tab-pane {{ $activeTab === 'profile' ? 'show active' : '' }}" id="account-profile" role="tabpanel">
        <form action="{{ route('account.profile.update') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User Account Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="name">Full Name</label>
                                        <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="email">Email Address</label>
                                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="user_role">User Role</label>
                                        <input class="form-control bg-light-subtle" id="user_role" type="text" value="Administrator" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="phone">Phone Number</label>
                                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="+254..." type="tel" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="job_title">Job Title</label>
                                        <input class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" placeholder="e.g. Sales Manager" type="text" value="{{ old('job_title', $user->job_title) }}">
                                        @error('job_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div>
                                        <label class="form-label" for="signature_upload">E-Signature</label>
                                        <div class="d-flex flex-wrap align-items-center gap-3 mb-3 {{ $hasSignature ? '' : 'd-none' }}" id="signature-preview-wrap">
                                            <img alt="E-signature preview" class="signature-preview border rounded p-2" data-empty-src="{{ $emptySignaturePreview }}" data-saved-src="{{ $hasSignature ? $signatureUrl : '' }}" id="signature-preview" src="{{ $hasSignature ? $signatureUrl : $emptySignaturePreview }}">
                                            @if($hasSignature)
                                                <button class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 {{ $signatureEditorOpen ? 'd-none' : '' }}" id="signature-create-new" type="button">
                                                    <i class="icon-sm" data-lucide="pen-line"></i>Create New Sign
                                                </button>
                                            @endif
                                        </div>
                                        <div class="{{ $signatureEditorOpen ? '' : 'd-none' }}" id="signature-editor">
                                            <div class="mb-3">
                                                <input accept="image/png,image/jpeg,image/webp" class="form-control @error('signature_upload') is-invalid @enderror" id="signature_upload" name="signature_upload" type="file">
                                                @error('signature_upload')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <input id="signature_data" name="signature_data" type="hidden" value="">
                                            <canvas aria-label="Create e-signature" class="signature-pad @error('signature_data') is-invalid @enderror" height="180" id="signature-pad" tabindex="0" width="640"></canvas>
                                            @error('signature_data')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                                                <p class="text-muted mb-0">Upload a PNG/JPG or sign inside the box, then save changes.</p>
                                                <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" id="signature-clear" type="button">
                                                    <i class="icon-sm" data-lucide="rotate-ccw"></i>Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Profile Photo</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-4">
                                    <div class="mb-3 mb-lg-0 text-center text-lg-start">
                                        @if($hasAvatar)
                                            <img alt="{{ $user->name }}" class="account-avatar account-avatar-image rounded-circle border bg-light" src="{{ $avatarUrl }}">
                                        @else
                                            <span aria-label="{{ $user->name }} initials" class="account-avatar account-avatar-initials rounded-circle border bg-primary-subtle text-primary">{{ $avatarInitials }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label class="form-label" for="avatar">Upload Photo</label>
                                        <input accept="image/png,image/jpeg,image/webp" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" type="file">
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <p class="text-muted mb-0">Use JPG, PNG, or WebP up to 2 MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Account Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="account-summary-item">
                                <p class="text-muted mb-1">Email Status</p>
                                <h6 class="mb-0">{{ $user->email_verified_at ? 'Verified on ' . $user->email_verified_at->format('d M, Y') : 'Pending verification' }}</h6>
                            </div>
                            <div class="account-summary-item">
                                <p class="text-muted mb-1">Joined</p>
                                <h6 class="mb-0">{{ $user->created_at?->format('d M, Y') ?? 'N/A' }}</h6>
                            </div>
                            <div class="account-summary-item">
                                <p class="text-muted mb-1">Last Updated</p>
                                <h6 class="mb-0">{{ $user->updated_at?->diffForHumans() ?? 'N/A' }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <a class="btn btn-outline-secondary" href="{{ route('account.show') }}">Cancel</a>
                        <button class="btn btn-success" type="submit"><i class="icon-sm me-1" data-lucide="save"></i>Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="tab-pane {{ $activeTab === 'security' ? 'show active' : '' }}" id="account-security" role="tabpanel">
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between gap-2">
                        <h4 class="card-title mb-0">Two-Factor Authentication</h4>
                        <span class="badge badge-soft-{{ $user->two_factor_enabled ? 'success' : 'secondary' }}">
                            {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" id="two-factor-toggle" type="checkbox" role="switch" @checked($user->two_factor_enabled) disabled>
                            <label class="form-check-label fw-medium" for="two-factor-toggle">Enable Two-Factor Authentication</label>
                        </div>
                        <p class="text-muted mb-3">Require a 6-digit email code after password login.</p>

                        @if(! $user->two_factor_enabled)
                            <form action="{{ route('account.two-factor.request') }}" method="POST" class="{{ $twoFactorPending ? 'mb-3' : '' }}">
                                @csrf
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="icon-sm me-1" data-lucide="mail-check"></i>Send Test Code
                                </button>
                            </form>

                            @if($twoFactorPending)
                                <form action="{{ route('account.two-factor.confirm') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="two_factor_otp">Verification Code</label>
                                        <input autocomplete="one-time-code" class="form-control @error('otp') is-invalid @enderror" id="two_factor_otp" inputmode="numeric" maxlength="6" name="otp" pattern="[0-9]{6}" placeholder="123456" type="text" value="{{ old('otp') }}" required>
                                        @error('otp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button class="btn btn-success" type="submit">
                                        <i class="icon-sm me-1" data-lucide="shield-check"></i>Confirm and Enable
                                    </button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('account.two-factor.disable') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="mb-3">
                                    <label class="form-label" for="disable_two_factor_password">Current Password</label>
                                    <input autocomplete="current-password" class="form-control @error('current_password') is-invalid @enderror" id="disable_two_factor_password" name="current_password" type="password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button class="btn btn-outline-danger" type="submit">
                                    <i class="icon-sm me-1" data-lucide="shield-off"></i>Disable Two-Factor
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Password Settings</h4>
                    </div>
                    <form action="{{ route('account.security.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="current_password">Current Password</label>
                                <input autocomplete="current-password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" type="password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">New Password</label>
                                <input autocomplete="new-password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                <input autocomplete="new-password" class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button class="btn btn-success" type="submit"><i class="icon-sm me-1" data-lucide="shield-check"></i>Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Security Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 pb-3 border-bottom">
                            <span class="avatar-sm rounded-circle bg-{{ $user->email_verified_at ? 'success' : 'warning' }}-subtle text-{{ $user->email_verified_at ? 'success' : 'warning' }} d-flex align-items-center justify-content-center">
                                <i data-lucide="{{ $user->email_verified_at ? 'badge-check' : 'mail-warning' }}"></i>
                            </span>
                            <div>
                                <h6 class="mb-1">Email Verification</h6>
                                <p class="text-muted mb-0">{{ $user->email_verified_at ? 'Verified on ' . $user->email_verified_at->format('d M, Y') : 'Verification is still pending.' }}</p>
                            </div>
                        </div>
                        @unless($user->email_verified_at)
                            <form action="{{ route('verification.send') }}" class="pt-3" method="POST">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" type="submit"><i class="icon-sm me-1" data-lucide="send"></i>Send Verification</button>
                            </form>
                        @endunless
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Sessions</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border d-flex align-items-start gap-2" role="status">
                            <i class="icon-sm mt-1" data-lucide="clock"></i>
                            <div>{{ $lastLoginText }}</div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">IP Address</label>
                                    <input class="form-control" readonly type="text" value="{{ $securityContext['ip_address'] }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Signed In Email</label>
                                    <input class="form-control" readonly type="text" value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div>
                                    <label class="form-label">Device</label>
                                    <textarea class="form-control bg-light-subtle" readonly rows="3">{{ $securityContext['user_agent'] }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <h6 class="mb-0">Active Sessions</h6>
                                @unless($canListSessions)
                                    <span class="badge badge-soft-secondary">Current device only</span>
                                @endunless
                            </div>
                            <div class="list-group mb-3">
                                @foreach($activeSessions as $session)
                                    <div class="list-group-item">
                                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                            <div class="me-2">
                                                <p class="mb-1 fw-medium">{{ $session['ip_address'] }} @if($session['is_current'])<span class="badge badge-soft-success ms-1">Current</span>@endif</p>
                                                <p class="text-muted mb-0 small">{{ $session['user_agent'] }}</p>
                                            </div>
                                            <small class="text-muted">{{ $session['last_active']->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('account.sessions.logout-other-devices') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="logout_devices_password">Current Password</label>
                                    <input autocomplete="current-password" class="form-control @error('current_password') is-invalid @enderror" id="logout_devices_password" name="current_password" type="password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="icon-sm me-1" data-lucide="log-out"></i>Log out all other devices
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');

        if (!canvas) {
            return;
        }

        const context = canvas.getContext('2d');
        const signatureInput = document.getElementById('signature_data');
        const clearButton = document.getElementById('signature-clear');
        const createButton = document.getElementById('signature-create-new');
        const editor = document.getElementById('signature-editor');
        const signaturePreview = document.getElementById('signature-preview');
        const signaturePreviewWrap = document.getElementById('signature-preview-wrap');
        const uploadInput = document.getElementById('signature_upload');
        const form = canvas.closest('form');
        let isDrawing = false;
        let hasInk = false;
        let previewFrame = null;
        let previewObjectUrl = null;

        context.lineWidth = 3;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#111827';

        const revokePreviewObjectUrl = () => {
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }
        };

        const showSignaturePreview = (src) => {
            if (!signaturePreview || !signaturePreviewWrap) {
                return;
            }

            signaturePreview.src = src;
            signaturePreviewWrap.classList.remove('d-none');
        };

        const restoreSavedPreview = () => {
            if (!signaturePreview || !signaturePreviewWrap) {
                return;
            }

            revokePreviewObjectUrl();

            if (signaturePreview.dataset.savedSrc) {
                showSignaturePreview(signaturePreview.dataset.savedSrc);
                return;
            }

            signaturePreview.src = signaturePreview.dataset.emptySrc;
            signaturePreviewWrap.classList.add('d-none');
        };

        const updateDrawPreview = () => {
            if (!hasInk || previewFrame) {
                return;
            }

            previewFrame = window.requestAnimationFrame(function () {
                const dataUrl = canvas.toDataURL('image/png');

                revokePreviewObjectUrl();
                signatureInput.value = dataUrl;
                showSignaturePreview(dataUrl);
                previewFrame = null;
            });
        };

        const pointFromEvent = (event) => {
            const rect = canvas.getBoundingClientRect();

            return {
                x: (event.clientX - rect.left) * (canvas.width / rect.width),
                y: (event.clientY - rect.top) * (canvas.height / rect.height),
            };
        };

        const startDrawing = (event) => {
            event.preventDefault();
            uploadInput.value = '';
            revokePreviewObjectUrl();
            isDrawing = true;
            hasInk = true;

            const point = pointFromEvent(event);

            context.beginPath();
            context.moveTo(point.x, point.y);
            canvas.setPointerCapture(event.pointerId);
        };

        const draw = (event) => {
            if (!isDrawing) {
                return;
            }

            event.preventDefault();

            const point = pointFromEvent(event);

            context.lineTo(point.x, point.y);
            context.stroke();
            updateDrawPreview();
        };

        const stopDrawing = () => {
            if (isDrawing) {
                updateDrawPreview();
            }

            isDrawing = false;
        };

        const clearSignature = (clearUpload = false) => {
            context.clearRect(0, 0, canvas.width, canvas.height);
            signatureInput.value = '';
            hasInk = false;
            restoreSavedPreview();

            if (clearUpload) {
                uploadInput.value = '';
            }
        };

        canvas.addEventListener('pointerdown', startDrawing);
        canvas.addEventListener('pointermove', draw);
        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);
        canvas.addEventListener('pointercancel', stopDrawing);

        if (createButton && editor) {
            createButton.addEventListener('click', function () {
                editor.classList.remove('d-none');
                createButton.classList.add('d-none');
                canvas.focus();
            });
        }

        clearButton.addEventListener('click', function () {
            clearSignature(true);
        });

        uploadInput.addEventListener('change', function () {
            if (uploadInput.files.length > 0) {
                context.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
                hasInk = false;
                revokePreviewObjectUrl();

                previewObjectUrl = URL.createObjectURL(uploadInput.files[0]);
                showSignaturePreview(previewObjectUrl);
                return;
            }

            restoreSavedPreview();
        });

        form.addEventListener('submit', function () {
            signatureInput.value = hasInk ? canvas.toDataURL('image/png') : '';
        });
    });
</script>
@endsection
