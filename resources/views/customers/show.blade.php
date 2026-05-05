@extends('layouts.vertical', ['title' => 'Customer Details'])

@section('content')
<div class="row justify-content-center">
     <div class="col-xl-8">
          <div class="card">
               <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                         <div>
                              <p class="text-muted mb-1">Customer</p>
                              <h4 class="mb-1">{{ $customer->full_name }}</h4>
                              <p class="text-muted mb-0">{{ $customer->reference() }}</p>
                         </div>
                         <div class="d-flex flex-wrap gap-2">
                              <a class="btn btn-outline-primary btn-sm" href="mailto:{{ $customer->email }}">Send Email</a>
                              @if($customer->whatsappUrl())
                              <a class="btn btn-success btn-sm" href="{{ $customer->whatsappUrl() }}" target="_blank" rel="noopener">WhatsApp</a>
                              @endif
                              <a class="btn btn-primary btn-sm" href="{{ $customer->telLink() }}">Call</a>
                         </div>
                    </div>

                    <div class="row g-3 mt-3">
                         <div class="col-md-4">
                              <div class="border rounded p-3 h-100">
                                   <p class="text-muted mb-1">Email</p>
                                   <div class="fw-semibold">{{ $customer->email }}</div>
                              </div>
                         </div>
                         <div class="col-md-4">
                              <div class="border rounded p-3 h-100">
                                   <p class="text-muted mb-1">Phone</p>
                                   <div class="fw-semibold">{{ $customer->phone }}</div>
                              </div>
                         </div>
                         <div class="col-md-4">
                              <div class="border rounded p-3 h-100">
                                   <p class="text-muted mb-1">Date</p>
                                   <div class="fw-semibold">{{ $customerDate }}</div>
                              </div>
                         </div>
                         <div class="col-md-4">
                              <div class="border rounded p-3 h-100">
                                   <p class="text-muted mb-1">Latest Service</p>
                                   <div class="fw-semibold">{{ $customer->latestServiceLabel() }}</div>
                                   <small class="text-muted d-block mt-1">{{ $customer->latestRouteSummary() }}</small>
                              </div>
                         </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                         <a class="btn btn-outline-secondary" href="{{ route('any', 'customers') }}">Back to Customers</a>
                         <a class="btn btn-outline-primary" href="{{ route('customers.edit', $customer) }}">Edit Customer</a>
                         <form action="{{ route('customers.destroy', $customer) }}" data-delete-confirm data-delete-message="Do you want to delete this customer and all linked quote requests?" data-delete-title="Delete customer?" method="POST">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-outline-danger" type="submit">Delete Customer</button>
                         </form>
                    </div>
               </div>
          </div>
     </div>
</div>
@endsection
