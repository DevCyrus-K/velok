@extends('layouts.auth', ['title' => 'Verify Email'])

@php
     $codeDigits = str_split(preg_replace('/\D+/', '', old('code', '')));
@endphp

@section('content')
<div class="col-xl-5">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="logo-dark" href="{{ route('second', [ 'dashboard' , 'index']) }}">
                              <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                         </a>
                         <a class="logo-light" href="{{ route('second', [ 'dashboard' , 'index']) }}">
                              <img alt="logo light" height="30" src="/images/logo-white.png" />
                         </a>
                    </div>

                    <div class="text-center">
                         <h3 class="fw-bold text-dark fs-20">Verify Your Email</h3>
                         <p class="text-muted mt-1 mb-4">Enter the 5-digit code we sent to {{ $maskedEmail }}. The code expires in {{ $ttlMinutes }} minutes.</p>
                    </div>

                    <div class="p-3">
                         @if ($errors->any())
                         @foreach ($errors->all() as $error)
                         <p class="text-danger mb-3">{{ $error }}</p>
                         @endforeach
                         @endif

                         <form method="POST" action="{{ route('verification.check') }}" class="authentication-form" id="verification-form">
                              @csrf
                              <input type="hidden" name="code" id="verification-code" value="{{ old('code') }}">

                              <div class="mb-4">
                                   <label class="form-label d-block text-center" for="verification-digit-1">Verification Code</label>
                                   <div class="d-flex align-items-center justify-content-center gap-2">
                                        @for ($i = 0; $i < 5; $i++)
                                        <input
                                             class="form-control form-control-lg rounded text-center fw-bold"
                                             id="verification-digit-{{ $i + 1 }}"
                                             type="text"
                                             inputmode="numeric"
                                             pattern="[0-9]*"
                                             maxlength="1"
                                             autocomplete="one-time-code"
                                             data-verification-digit
                                             value="{{ $codeDigits[$i] ?? '' }}"
                                             style="max-width: 56px;"
                                        />
                                        @endfor
                                   </div>
                              </div>

                              <div class="text-center d-grid mb-3">
                                   <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                        <i class="fs-18" data-lucide="shield-check"></i> Submit Code
                                   </button>
                              </div>
                         </form>

                         <form method="POST" action="{{ route('verification.send') }}" class="text-center">
                              @csrf
                              <button class="btn btn-link p-0 fw-semibold text-decoration-none" type="submit">Resend 5-digit code</button>
                         </form>
                    </div>

                    <p class="text-muted text-center mt-4 mb-0">Wrong account? <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('logout') }}" onclick="event.preventDefault(); const form = document.getElementById('verify-logout-form'); form.requestSubmit ? form.requestSubmit() : form.submit();">Log out</a></p>
                    <form id="verify-logout-form" action="{{ route('logout') }}" method="POST" class="d-none" data-action-message="Logging you out...">
                         @csrf
                    </form>
               </div>
          </div>
     </div>
</div>
@endsection

@section('scripts')
<script>
     document.addEventListener('DOMContentLoaded', function () {
          const inputs = Array.from(document.querySelectorAll('[data-verification-digit]'));
          const hiddenInput = document.getElementById('verification-code');
          const form = document.getElementById('verification-form');

          if (!inputs.length || !hiddenInput || !form) {
               return;
          }

          const syncCode = () => {
               hiddenInput.value = inputs.map((input) => input.value.replace(/\D/g, '')).join('');
          };

          inputs.forEach((input, index) => {
               input.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 1);
                    syncCode();

                    if (this.value && inputs[index + 1]) {
                         inputs[index + 1].focus();
                         inputs[index + 1].select();
                    }
               });

               input.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && !this.value && inputs[index - 1]) {
                         inputs[index - 1].focus();
                         inputs[index - 1].select();
                    }
               });

               input.addEventListener('paste', function (event) {
                    const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 5);

                    if (!pasted) {
                         return;
                    }

                    event.preventDefault();

                    pasted.split('').forEach((digit, digitIndex) => {
                         if (inputs[digitIndex]) {
                              inputs[digitIndex].value = digit;
                         }
                    });

                    syncCode();
                    const nextInput = inputs[Math.min(pasted.length, inputs.length - 1)];
                    nextInput.focus();
                    nextInput.select();
               });
          });

          form.addEventListener('submit', syncCode);
          syncCode();
          inputs[0].focus();
          inputs[0].select();
     });
</script>
@endsection
