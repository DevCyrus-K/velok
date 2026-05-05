@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
<div class="col-xl-5 col-lg-6">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3 p-sm-4">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="logo-dark" href="{{ route('second', ['dashboard', 'index']) }}">
                              <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                         </a>
                         <a class="logo-light" href="{{ route('second', ['dashboard', 'index']) }}">
                              <img alt="logo light" height="30" src="/images/logo-white.png" />
                         </a>
                    </div>

                    <div class="text-center mb-4">
                         <h3 class="fw-bold text-dark fs-20 mb-2">Set a New Password</h3>
                         <p class="text-muted mb-0">Choose a new password for your account, then confirm it to finish the reset.</p>
                    </div>

                    <form method="POST" action="{{ route('password.reset-confirmed') }}" id="reset-password-form" class="authentication-form" novalidate>
                         @csrf
                         <input type="hidden" name="email" value="{{ $email }}">
                         <input type="hidden" name="code_token" value="{{ $codeToken }}">

                         @if ($errors->any())
                         @foreach ($errors->all() as $error)
                         <p class="text-danger mb-3">{{ $error }}</p>
                         @endforeach
                         @endif

                         <div class="mb-4">
                              <label class="form-label" for="new-password">New Password</label>
                              <input
                                   class="form-control form-control-lg rounded"
                                   id="new-password"
                                   name="password"
                                   type="password"
                                   autocomplete="new-password"
                                   required
                                   minlength="8"
                                   placeholder="Enter your new password"
                              />
                         </div>

                         <div class="mb-3">
                              <label class="form-label" for="confirm-password">Confirm Password</label>
                              <input
                                   class="form-control form-control-lg rounded"
                                   id="confirm-password"
                                   name="password_confirmation"
                                   type="password"
                                   autocomplete="new-password"
                                   required
                                   minlength="8"
                                   placeholder="Confirm your new password"
                              />
                         </div>

                         <p class="text-danger mb-3 d-none" id="password-match-feedback">Passwords do not match.</p>

                         <div class="d-grid">
                              <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit"><i class="fs-18" data-lucide="save"></i> Reset Password</button>
                         </div>
                    </form>

                    <p class="text-muted text-center mt-4 mb-0">Back to <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('login') }}">Sign In</a></p>
               </div>
          </div>
     </div>
</div>
@endsection

@section('scripts')
<script>
     document.addEventListener('DOMContentLoaded', function () {
          const form = document.getElementById('reset-password-form');
          const passwordInput = document.getElementById('new-password');
          const confirmInput = document.getElementById('confirm-password');
          const feedback = document.getElementById('password-match-feedback');

          if (!form || !passwordInput || !confirmInput || !feedback) {
               return;
          }

          const syncValidation = () => {
               const matches = passwordInput.value === confirmInput.value;
               const showMismatch = confirmInput.value.length > 0 && !matches;

               confirmInput.setCustomValidity(matches ? '' : 'Passwords do not match.');
               feedback.classList.toggle('d-none', !showMismatch);

               return matches;
          };

          passwordInput.addEventListener('input', syncValidation);
          confirmInput.addEventListener('input', syncValidation);

          form.addEventListener('submit', (event) => {
               if (!syncValidation()) {
                    event.preventDefault();
               }
          });
     });
</script>
@endsection
