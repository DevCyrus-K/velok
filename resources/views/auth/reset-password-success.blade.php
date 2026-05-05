@extends('layouts.auth', ['title' => 'Password Reset Successful'])

@section('content')
<div class="col-xl-5 col-lg-6">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3 p-sm-4 text-center">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="logo-dark" href="{{ route('second', ['dashboard', 'index']) }}">
                              <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                         </a>
                         <a class="logo-light" href="{{ route('second', ['dashboard', 'index']) }}">
                              <img alt="logo light" height="30" src="/images/logo-white.png" />
                         </a>
                    </div>

                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 72px; height: 72px;">
                         <i class="fs-32" data-lucide="check"></i>
                    </div>

                    <h3 class="fw-bold text-dark fs-20 mb-2">Password reset successful!</h3>
                    <p class="text-muted mb-0">Redirecting you to the login page in a moment.</p>
                    <p class="text-muted mt-4 mb-0">If nothing happens, <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ $redirectUrl }}">sign in here</a>.</p>
               </div>
          </div>
     </div>
</div>
@endsection

@section('scripts')
<script>
     document.addEventListener('DOMContentLoaded', function () {
          window.setTimeout(function () {
               window.location.assign(@json($redirectUrl));
          }, {{ $redirectDelayMs }});
     });
</script>
@endsection
