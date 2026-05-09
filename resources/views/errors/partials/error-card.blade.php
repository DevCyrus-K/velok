@php
    $appName = config('app.name', 'Kwikshift Admin Panel');
    $homeUrl = url('/');
    $primaryHref = $primaryHref ?? $homeUrl;
    $primaryLabel = $primaryLabel ?? 'Back to Home';
    $primaryClass = $primaryClass ?? 'btn-success';
@endphp

<div class="col-xl-6">
    <div class="card auth-card">
        <div class="card-body p-0">
            <div class="row align-items-center g-0">
                <div class="col">
                    <div class="p-4">
                        <div class="mx-auto mb-4 text-center">
                            <div class="mx-auto text-center auth-logo">
                                <a class="logo-dark" href="{{ $homeUrl }}">
                                    <img alt="{{ $appName }} logo" height="30" src="/images/logo-dark.png" />
                                </a>
                                <a class="logo-light" href="{{ $homeUrl }}">
                                    <img alt="{{ $appName }} logo" height="30" src="/images/logo-white.png" />
                                </a>
                            </div>

                            @if(! empty($image))
                                <img alt="{{ $imageAlt ?? $heading }}" class="mt-5 mb-3 img-fluid" height="250" src="{{ $image }}">
                            @elseif(! empty($icon))
                                <div class="mt-5 mb-3 {{ $iconClass ?? 'text-primary' }}">
                                    <i data-lucide="{{ $icon }}" style="width: 100px; height: 100px;"></i>
                                </div>
                            @endif

                            <h2 class="fs-22 lh-base fw-bold">{{ $heading }}</h2>
                            <p class="text-muted mt-1 mb-4">{{ $message }}</p>

                            <div class="text-center">
                                <a class="btn {{ $primaryClass }}" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>

                                @if(! empty($secondaryLabel) && ! empty($secondaryHref))
                                    <a class="btn {{ $secondaryClass ?? 'btn-light' }} ms-2" href="{{ $secondaryHref }}">{{ $secondaryLabel }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
