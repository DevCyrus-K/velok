@extends('layouts.auth', ['title' => 'Sign Up'])

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
                         <h3 class="fw-bold text-dark fs-20">Create Your Account</h3>
                         <p class="text-muted mt-1 mb-4">Sign up with your full name, email, and password to continue.</p>
                    </div>
                    <div class="p-3">
                         <form method="POST" action="{{ route('register') }}" class="authentication-form">
                              @csrf
                              @if ($errors->any())
                              @foreach ($errors->all() as $error)
                              <p class="text-danger mb-3">{{ $error }}</p>
                              @endforeach
                              @endif

                              <div class="mb-4">
                                   <label class="form-label" for="register-name">Full Name</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" id="register-name" name="name" value="{{ old('name') }}" type="text" autocomplete="name" required placeholder="Enter your full name" />
                                        <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:user-bold-duotone"></iconify-icon>
                                        </p>
                                   </div>
                              </div>

                              <div class="mb-4">
                                   <label class="form-label" for="register-email">Email</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" id="register-email" name="email" value="{{ old('email') }}" type="email" autocomplete="email" required placeholder="Enter your email" />
                                        <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:letter-bold-duotone"></iconify-icon>
                                        </p>
                                   </div>
                              </div>

                              <div class="mb-4">
                                   <label class="form-label" for="register-password">Password</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" id="register-password" name="password" type="password" autocomplete="new-password" required placeholder="Create a password" />
                                        <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                   </div>
                              </div>

                              <div class="mb-4">
                                   <label class="form-label" for="register-password-confirmation">Confirm Password</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required placeholder="Confirm your password" />
                                        <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                   </div>
                              </div>

                              <div class="text-center d-grid">
                                   <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                        <i class="fs-18" data-lucide="user-plus"></i> Sign Up
                                   </button>
                              </div>
                         </form>
                    </div>
                    <p class="text-muted text-center mt-4 mb-0">I already have an account <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('login') }}">Sign In</a></p>
               </div>
          </div>
     </div>
</div>
@endsection
