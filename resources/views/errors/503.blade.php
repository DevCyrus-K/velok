@extends('layouts.auth', ['title' => '503 - Service Unavailable'])

@section('content')
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6">
                <div class="card auth-card">
                    <div class="card-body p-0">
                        <div class="row align-items-center g-0">
                            <div class="col">
                                <div class="p-4">
                                    <div class="mx-auto mb-4 text-center">
                                        <div class="mx-auto text-center auth-logo">
                                            <a class="logo-dark" href="{{ route('second', ['dashboard', 'index'])}}">
                                                <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                                            </a>
                                            <a class="logo-light" href="{{ route('second', ['dashboard', 'index'])}}">
                                                <img alt="logo light" height="30" src="/images/logo-white.png" />
                                            </a>
                                        </div>
                                        <div class="mt-5 mb-3 text-primary">
                                            <i data-lucide="wifi-off" style="width: 100px; height: 100px;"></i>
                                        </div>
                                        <h2 class="fs-22 lh-base fw-bold">Service Unavailable</h2>
                                        <p class="text-muted mt-1 mb-4">The service is temporarily unavailable. <br /> Please try again in a few moments.</p>
                                        <div class="text-center">
                                            <a class="btn btn-primary" href="javascript:location.reload()">Retry</a>
                                            <a class="btn btn-success ms-2" href="{{ route('second', ['dashboard', 'index'])}}">Back to Home</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
@endsection
