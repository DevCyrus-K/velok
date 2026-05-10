@extends('layouts.vertical', ['title' => 'Add User'])

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $statusOptions = $statusOptions ?? \App\Models\Customer::statusOptions();
@endphp

@if (($errors ?? null)?->any())
    <div class="alert alert-danger" role="alert">
         <div class="fw-semibold mb-2">Please fix the highlighted fields.</div>
         <ul class="mb-0 ps-3">
              @foreach (($errors ?? collect())->all() as $error)
                   <li>{{ $error }}</li>
              @endforeach
         </ul>
    </div>
@endif

<div class="row justify-content-center">
     <div class="col-xl-8">
          <form action="{{ route('customers.store') }}" method="POST">
               @csrf

               <div class="card">
                    <div class="card-body">
                         <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                              <div class="d-flex align-items-center gap-2">
                                   <span class="avatar-sm rounded bg-success-subtle text-success d-inline-flex align-items-center justify-content-center">
                                        <i data-lucide="user-plus" class="icon-sm"></i>
                                   </span>
                                   <h4 class="card-title mb-0">Add User</h4>
                              </div>
                              <a class="btn btn-sm btn-outline-secondary" href="{{ route('any', 'customers') }}">
                                   <i data-lucide="arrow-left" class="icon-sm me-1"></i>Customers
                              </a>
                         </div>

                         <div class="row g-3">
                              <div class="col-md-6">
                                   <label class="form-label" for="full_name">Customer Name</label>
                                   <input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" autocomplete="name" required>
                                   @error('full_name')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="email">Customer Email</label>
                                   <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                                   @error('email')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="phone">Phone Number</label>
                                   <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required>
                                   @error('phone')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="status">Status</label>
                                   <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        @foreach($statusOptions as $status => $label)
                                        <option value="{{ $status }}" @selected(old('status', \App\Models\Customer::STATUS_LEAD) === $status)>{{ $label }}</option>
                                        @endforeach
                                   </select>
                                   @error('status')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                         </div>

                         <div class="d-flex flex-wrap gap-2 mt-4">
                              <button class="btn btn-success" type="submit">
                                   <i data-lucide="save" class="icon-sm me-1"></i>Save User
                              </button>
                              <a class="btn btn-outline-secondary" href="{{ route('any', 'customers') }}">Cancel</a>
                         </div>
                    </div>
               </div>
          </form>
     </div>
</div>
@endsection
