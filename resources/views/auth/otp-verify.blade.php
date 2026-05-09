@extends('layouts.auth', ['title' => 'Verify Login'])

@php
     $codeDigits = str_split(preg_replace('/\D+/', '', old('otp', '')));
@endphp

@section('content')
<div class="col-xl-5 col-lg-6">
     <div class="card auth-card">
          <div class="card-body">
               <div class="p-3 p-sm-4">
                    <div class="mx-auto mb-5 auth-logo text-center">
                         <a class="logo-dark" href="{{ route('login') }}">
                              <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                         </a>
                         <a class="logo-light" href="{{ route('login') }}">
                              <img alt="logo light" height="30" src="/images/logo-white.png" />
                         </a>
                    </div>

                    <div class="text-center mb-4">
                         <h3 class="fw-bold text-dark fs-20 mb-2">Enter Verification Code</h3>
                         <p class="text-muted mb-0">Enter the 6-digit code sent to {{ $maskedEmail }}.</p>
                    </div>

                    <form method="POST" action="{{ route('otp.verify.store') }}" id="otp-form" class="authentication-form">
                         @csrf
                         <input type="hidden" name="otp" id="otp-code" value="{{ old('otp', '') }}">

                         @if ($errors->any())
                         @foreach ($errors->all() as $error)
                         <p class="text-danger mb-3 text-center">{{ $error }}</p>
                         @endforeach
                         @endif

                         <div class="mb-3">
                              <label class="form-label d-block text-center" for="otp-digit-1">Verification Code</label>
                              <div class="d-flex gap-2 justify-content-center">
                                   @for ($i = 0; $i < 6; $i++)
                                   <input
                                        class="form-control form-control-lg text-center fw-bold"
                                        data-otp-digit
                                        @if($i === 0) id="otp-digit-1" @endif
                                        inputmode="numeric"
                                        maxlength="1"
                                        pattern="\d"
                                        placeholder="0"
                                        type="text"
                                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        value="{{ $codeDigits[$i] ?? '' }}"
                                        style="max-width: 52px;"
                                   />
                                   @endfor
                              </div>
                              <p class="text-danger text-center mt-2 mb-0 d-none" id="otp-feedback">Enter the full 6-digit code to continue.</p>
                         </div>

                         <div class="d-grid mt-4">
                              <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                   <i class="fs-18" data-lucide="shield-check"></i> Verify Login
                              </button>
                         </div>
                    </form>

                    <form method="POST" action="{{ route('otp.resend') }}" class="text-center mt-3">
                         @csrf
                         <button class="btn btn-link p-0 fw-semibold text-decoration-none" type="submit" data-resend-button data-wait="{{ $resendAvailableIn }}" data-maxed="{{ $resendCount >= $maxResends ? '1' : '0' }}" @disabled($resendAvailableIn > 0 || $resendCount >= $maxResends)>
                              Resend code{{ $resendAvailableIn > 0 ? ' in '.$resendAvailableIn.'s' : '' }}
                         </button>
                         <p class="text-muted mt-2 mb-0 small">{{ $resendCount }} of {{ $maxResends }} resends used</p>
                    </form>
               </div>
          </div>
     </div>
</div>
@endsection

@section('scripts')
<script>
     document.addEventListener('DOMContentLoaded', function () {
          const inputs = Array.from(document.querySelectorAll('[data-otp-digit]'));
          const hiddenInput = document.getElementById('otp-code');
          const feedback = document.getElementById('otp-feedback');
          const form = document.getElementById('otp-form');
          const resendButton = document.querySelector('[data-resend-button]');

          if (inputs.length && hiddenInput && feedback && form) {
               const syncCode = () => {
                    hiddenInput.value = inputs.map((input) => input.value.replace(/\D/g, '')).join('');
                    const complete = hiddenInput.value.length === 6;
                    feedback.classList.toggle('d-none', complete || hiddenInput.value.length === 0);

                    return complete;
               };

               inputs.forEach((input, index) => {
                    input.addEventListener('input', (event) => {
                         event.target.value = event.target.value.replace(/\D/g, '').slice(0, 1);

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
                              .slice(0, 6 - index)
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
               inputs[0].focus();
          }

          if (resendButton) {
               let seconds = parseInt(resendButton.dataset.wait || '0', 10);
               const originalText = 'Resend code';

               if (resendButton.dataset.maxed === '1') {
                    return;
               }

               const tick = () => {
                    if (seconds <= 0) {
                         resendButton.disabled = false;
                         resendButton.textContent = originalText;
                         return;
                    }

                    resendButton.disabled = true;
                    resendButton.textContent = `${originalText} in ${seconds}s`;
                    seconds -= 1;
                    window.setTimeout(tick, 1000);
               };

               tick();
          }
     });
</script>
@endsection
