@extends('layouts.vertical', ['title' => 'Edit Customer'])

@section('content')
@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
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
          <form action="{{ route('customers.update', $customer) }}" method="POST">
               @csrf
               @method('PUT')

               <div class="card">
                    <div class="card-body">
                         <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                              <div>
                                   <h4 class="card-title mb-1">Edit Customer</h4>
                                   <p class="text-muted mb-0">Update the customer name, email, and phone number.</p>
                              </div>
                              <a class="btn btn-sm btn-outline-secondary" href="{{ route('customers.show', $customer) }}">Back to View</a>
                         </div>

                         <div class="row g-3">
                              <div class="col-md-6">
                                   <label class="form-label" for="full_name">Customer Name</label>
                                   <input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" type="text" value="{{ old('full_name', $customer->full_name) }}">
                                   @error('full_name')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="email">Customer Email</label>
                                   <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $customer->email) }}">
                                   @error('email')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="phone">Phone Number</label>
                                   <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone) }}">
                                   @error('phone')
                                   <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                              </div>
                              <div class="col-md-6">
                                   <label class="form-label" for="customer_date">Date</label>
                                   <input class="form-control" id="customer_date" type="text" value="{{ $customerDate }}" readonly>
                              </div>
                         </div>

                         <div class="d-flex flex-wrap gap-2 mt-4">
                              <button class="btn btn-success" type="submit">Update Customer</button>
                              <a class="btn btn-outline-secondary" href="{{ route('customers.show', $customer) }}">Cancel</a>
                         </div>
                    </div>
               </div>
          </form>

          <div class="card border-danger border-opacity-25">
               <div class="card-body">
                    <h5 class="card-title text-danger mb-2">Delete Customer</h5>
                    <p class="text-muted mb-3">Remove this customer and all linked quote requests.</p>
                    <form action="{{ route('customers.destroy', $customer) }}" data-delete-confirm data-delete-message="Do you want to delete this customer and all linked quote requests?" data-delete-title="Delete customer?" method="POST">
                         @csrf
                         @method('DELETE')
                         <button class="btn btn-danger" type="submit">Delete Customer</button>
                    </form>
               </div>
          </div>
     </div>
</div>
@endsection
