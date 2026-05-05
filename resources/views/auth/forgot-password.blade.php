@extends('layouts.auth', ['title' => 'Create New Password'])

@section('content')
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
     <div class="container">
          <div class="row justify-content-center">
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
                                        <h3 class="fw-bold text-dark fs-20">Create a New Password</h3>
                                        <p class="text-muted mt-1 mb-4">Set a fresh password so you can get back into your account.</p>
                                   </div>
                                   <div class="p-3">
                                        <form method="POST" action="{{ route('password.update') }}" class="authentication-form">
                                             @csrf
                                             <input type="hidden" name="token" value="{{ $request->route('token') }}">
                                             @if (sizeof($errors) > 0)
                                             @foreach ($errors->all() as $error)
                                             <p class="text-danger mb-3">{{ $error }}</p>
                                             @endforeach
                                             @endif

                                             <div class="mb-3">
                                                  <label class="form-label" for="reset-email">Email</label>
                                                  <div class="position-relative w-100">
                                                       <input class="form-control form-control-lg rounded" id="reset-email" name="email" value="{{ old('email', $request->email) }}" placeholder="Enter Email" required="" type="email" />
                                                       <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                                            <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:letter-bold-duotone"></iconify-icon>
                                                       </p>
                                                  </div>
                                             </div>
                                             <div class="mb-3">
                                                  <label class="form-label" for="reset-password">Password</label>
                                                  <div class="position-relative w-100">
                                                       <input class="form-control form-control-lg rounded" id="reset-password" name="password" placeholder="Enter password" required="" type="password" />
                                                       <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button">
                                                            <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:eye-bold-duotone"></iconify-icon>
                                                       </button>
                                                  </div>
                                             </div>
                                             <div class="mb-3">
                                                  <label class="form-label" for="reset-password-confirmation">Confirm Password</label>
                                                  <div class="position-relative w-100">
                                                       <input class="form-control form-control-lg rounded" id="reset-password-confirmation" name="password_confirmation" placeholder="Confirm password" required="" type="password" />
                                                       <button class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2" type="button">
                                                            <iconify-icon class="fs-20 mt-1 text-muted" icon="solar:eye-bold-duotone"></iconify-icon>
                                                       </button>
                                                  </div>
                                             </div>
                                             <div class="mb-1 text-center d-grid">
                                                  <button class="btn btn-primary" type="submit">Save Password</button>
                                             </div>
                                        </form>
                                   </div>
                                   <p class="text-muted text-center mt-4 mb-0">Back to <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('login') }}">Sign In</a></p>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</div>
@endsection
