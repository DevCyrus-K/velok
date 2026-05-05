@extends('layouts.auth', ['title' => 'Forgot Password'])

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
                         <h3 class="fw-bold text-dark fs-20 mb-2">Forgot Password?</h3>
                         <p class="text-muted mb-0">Enter your email and we'll guide you through a quick code-based reset.</p>
                    </div>

                    <form method="POST" action="{{ route('password.email') }}" class="authentication-form">
                         @csrf

                         @if ($errors->any())
                         @foreach ($errors->all() as $error)
                         <p class="text-danger mb-3">{{ $error }}</p>
                         @endforeach
                         @endif

                         <div class="mb-4">
                              <label class="form-label" for="reset-email">Email Address</label>
                              <div class="position-relative w-100">
                                   <input
                                        class="form-control form-control-lg rounded"
                                        id="reset-email"
                                        name="email"
                                        value="{{ old('email', request('email')) }}"
                                        placeholder="Enter your email"
                                        required
                                        type="email"
                                        autocomplete="email"
                                   />
                                   <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                        <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:letter-bold-duotone"></iconify-icon>
                                   </p>
                              </div>
                         </div>

                         <div class="d-grid">
                              <button class="btn btn-primary" type="submit">Send Verification Code</button>
                         </div>
                    </form>

                    <p class="text-muted text-center mt-4 mb-0">Back to <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('login') }}">Sign In</a></p>
               </div>
          </div>
     </div>
</div>
@endsection
