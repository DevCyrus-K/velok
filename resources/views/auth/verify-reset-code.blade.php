@extends('layouts.auth', ['title' => 'Verify Code'])

@php
     $codeDigits = str_split(preg_replace('/\D+/', '', old('code', '')));
@endphp

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
                         <h3 class="fw-bold text-dark fs-20 mb-2">Enter Verification Code</h3>
                         <p class="text-muted mb-0">Enter the {{ $codeLength }}-digit code for {{ $maskedEmail }}. If this email is registered, it should arrive shortly.</p>
                    </div>

                    <form method="POST" action="{{ route('password.verify-code') }}" id="verification-form" class="authentication-form">
                         @csrf
                         <input type="hidden" name="email" value="{{ $email }}">
                         <input type="hidden" name="code" id="verification-code" value="{{ old('code', '') }}">

                         @if ($errors->any())
                         @foreach ($errors->all() as $error)
                         <p class="text-danger mb-3 text-center">{{ $error }}</p>
                         @endforeach
                         @endif

                         <div class="mb-3">
                              <label class="form-label d-block text-center" for="verification-digit-1">Verification Code</label>
                              <div class="d-flex gap-2 justify-content-center">
                                   @for ($i = 0; $i < $codeLength; $i++)
                                   <input
                                        class="form-control form-control-lg text-center"
                                        data-verification-digit
                                        id="{{ $i === 0 ? 'verification-digit-1' : null }}"
                                        inputmode="numeric"
                                        maxlength="1"
                                        pattern="\d"
                                        placeholder="0"
                                        type="text"
                                        value="{{ $codeDigits[$i] ?? '' }}"
                                   />
                                   @endfor
                              </div>
                              <p class="text-muted text-center mt-2 mb-0">This code expires in {{ $ttlMinutes }} minutes and can only be used once.</p>
                              <p class="text-danger text-center mt-2 mb-0 d-none" id="verification-code-feedback">Enter the full {{ $codeLength }}-digit code to continue.</p>
                         </div>

                         <div class="d-grid mt-4">
                              <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit"><i class="fs-18" data-lucide="check"></i> Verify Code</button>
                         </div>
                    </form>

                    <p class="text-muted text-center mt-4 mb-0">Need a new code? <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('password.request', ['email' => $email]) }}">Go back</a></p>
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
          const feedback = document.getElementById('verification-code-feedback');
          const form = document.getElementById('verification-form');
          const codeLength = {{ $codeLength }};

          if (!inputs.length || !hiddenInput || !feedback || !form) {
               return;
          }

          const syncCode = () => {
               hiddenInput.value = inputs.map((input) => input.value.replace(/\D/g, '')).join('');
               const complete = hiddenInput.value.length === codeLength;
               feedback.classList.toggle('d-none', complete || hiddenInput.value.length === 0);

               return complete;
          };

          inputs.forEach((input, index) => {
               input.addEventListener('input', (event) => {
                    event.target.value = event.target.value.replace(/\D/g, '');

                    if (event.target.value.length > 0 && index < inputs.length - 1) {
                         inputs[index + 1].focus();
                    }

                    syncCode();
               });

               input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && input.value.length === 0 && index > 0) {
                         inputs[index - 1].focus();
                    }
               });

               input.addEventListener('paste', (event) => {
                    event.preventDefault();
                    const digits = (event.clipboardData || window.clipboardData)
                         .getData('text')
                         .replace(/\D/g, '')
                         .slice(0, codeLength - index)
                         .split('');

                    digits.forEach((digit, digitIndex) => {
                         if (index + digitIndex < inputs.length) {
                              inputs[index + digitIndex].value = digit;
                         }
                    });

                    syncCode();
                    inputs[Math.min(index + digits.length, inputs.length - 1)].focus();
               });
          });

          form.addEventListener('submit', (event) => {
               if (!syncCode()) {
                    event.preventDefault();
               }
          });

          syncCode();
     });
</script>
@endsection
