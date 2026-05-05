@extends('layouts.auth', ['title' => 'Log in'])

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
                         <h3 class="fw-bold text-dark fs-20">Hi , Welcome Back 👋 </h3>
                         <p class="text-muted mt-1 mb-4">Enter your email and password to access your account.
                         </p>
                    </div>
                    <div class="p-3">
                         <form method="POST" action="{{ route('login')}}" class="authentication-form">
                              @csrf
                              @if (sizeof($errors) > 0)
                              @foreach ($errors->all() as $error)
                              <p class="text-danger mb-3">{{ $error }}</p>
                              @endforeach
                              @endif

                              <div class="mb-4">
                                   <label class="form-label" for="emailaddress">Email</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded" type="email" name="email" id="emailaddress" value="{{ old('email') }}" autocomplete="email" required placeholder="Enter your email"/>
                                        <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:letter-bold-duotone"></iconify-icon>
                                        </p>
                                   </div>
                              </div>
                              <div class="mb-4">
                                   <a class="float-end fw-semibold text-reset ms-1" href="{{ route('password.request') }}">Reset password</a>
                                   <label class="form-label" for="password">Password</label>
                                   <div class="position-relative w-100">
                                        <input class="form-control form-control-lg rounded"  type="password" required id="password" name="password" autocomplete="current-password" placeholder="Enter your password"/>
                                        <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button">
                                             <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:eye-bold-duotone"></iconify-icon>
                                        </button>
                                   </div>
                              </div>
                              <div class="mb-3">
                                   <div class="form-check">
                                        <input class="form-check-input" id="checkbox-signin" type="checkbox" name="remember" value="1" @checked(old('remember')) />
                                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                   </div>
                              </div>
                              <div class="text-center d-grid">
                                   <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit"><i class="fs-18" data-lucide="log-in"></i> Sign In</button>
                              </div>
                         </form>
                    </div>
                    <p class="text-muted text-center mt-4 mb-0">Don't have an account? <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('register') }}">Sign up</a> </p>
               </div> <!-- end col -->
          </div> <!-- end card-body -->
     </div> <!-- end card -->
</div>
@endsection
