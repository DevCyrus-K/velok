@extends('layouts.auth', ['title' => 'Lock Screen'])

@section('content')

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
     <div class="container">
          <div class="row justify-content-center">
               <div class="col-xl-5">
                    <div class="card auth-card">
                         <div class="card-body px-3 py-5">
                              <div class="mx-auto mb-5 auth-logo text-center">
                                   <a class="logo-dark" href="{{ route('second', [ 'dashboard' , 'index']) }}">
                                        <img alt="logo dark" height="30" src="/images/logo-dark.png" />
                                   </a>
                                   <a class="logo-light" href="{{ route('second', [ 'dashboard' , 'index']) }}">
                                        <img alt="logo light" height="30" src="/images/logo-white.png" />
                                   </a>
                              </div>
                              <h2 class="fw-bold text-center fs-18">Hi {{ $lockedUser->name }}</h2>
                              <p class="text-muted text-center mt-1 mb-4">Enter your password to continue.</p>
                              <div class="px-4">
                                   <form action="{{ route('lock-screen.unlock') }}" method="POST" class="authentication-form">
                                        @csrf
                                        @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                        <p class="text-danger mb-3">{{ $error }}</p>
                                        @endforeach
                                        @endif

                                        <div class="mb-3">
                                             <label class="form-label visually-hidden" for="lock-password">Password</label>
                                             <input autocomplete="current-password" class="form-control" id="lock-password" name="password" placeholder="Enter your password" type="password" required />
                                        </div>
                                        <div class="mb-1 text-center d-grid">
                                             <button class="btn btn-primary" type="submit">Unlock</button>
                                        </div>
                                   </form>
                              </div> <!-- end col -->
                         </div> <!-- end card-body -->
                    </div> <!-- end card -->
                    <p class="mb-0 text-center">Not you? <a class="link-primary fst-italic text-decoration-underline fw-semibold" href="{{ route('logout') }}" onclick="event.preventDefault(); const form = document.getElementById('lock-logout-form'); form.requestSubmit ? form.requestSubmit() : form.submit();">Log out</a></p>
                    <form id="lock-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                         @csrf
                    </form>
               </div> <!-- end col -->
          </div> <!-- end row -->
     </div>
</div>

@endsection
