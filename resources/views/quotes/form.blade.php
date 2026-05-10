@extends('layouts.vertical', ['title' => $isEditing ? 'Edit Quote' : 'Create Quote'])

@section('content')
@php
    $selectedStatus = old('status', $quote->status ?? 'new');
    $serviceTypeOptions = $serviceTypeOptions ?? \App\Models\QuoteRequest::serviceTypeOptions();
    $selectedServiceType = \App\Support\LeadCategory::serviceTypeLabel(
        old('service_type', $quote->serviceTypeLabel()),
        ''
    );
    $canDeleteQuote = $isEditing && $quote->statusGroup() !== 'approved';
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">Please fix the highlighted fields.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $isEditing ? route('quotes.update', $quote) : route('quotes.store') }}" method="POST">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h5 class="card-title mb-1">{{ $isEditing ? 'Edit Quote' : 'Create Quote' }}</h5>
                            <p class="text-muted mb-0">
                                {{ $isEditing ? 'Update this live quote from the kwikshift database.' : 'Create a new quote directly in the kwikshift database.' }}
                            </p>
                        </div>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('quotes.index') }}">Back to Quotes</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="full_name">Full Name</label>
                            <input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name"
                                type="text" value="{{ old('full_name', $quote->full_name) }}">
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email Address</label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                type="email" value="{{ old('email', $quote->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                                type="text" value="{{ old('phone', $quote->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Preferred Contact Method</label>
                            @php($selectedContactPreference = old('contact_preference', $quote->contact_preference ?? 'both'))
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" id="contact_preference_email" name="contact_preference" type="radio" value="email" @checked($selectedContactPreference === 'email')>
                                    <label class="form-check-label" for="contact_preference_email">Email</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" id="contact_preference_whatsapp" name="contact_preference" type="radio" value="whatsapp" @checked($selectedContactPreference === 'whatsapp')>
                                    <label class="form-check-label" for="contact_preference_whatsapp">WhatsApp</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" id="contact_preference_both" name="contact_preference" type="radio" value="both" @checked($selectedContactPreference === 'both')>
                                    <label class="form-check-label" for="contact_preference_both">Both</label>
                                </div>
                            </div>
                            @error('contact_preference')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="move_date">Preferred Move Date</label>
                            <input class="form-control @error('move_date') is-invalid @enderror" id="move_date" name="move_date"
                                type="date" value="{{ old('move_date', $quote->move_date?->format('Y-m-d')) }}">
                            @error('move_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="pickup_location">Pickup Location</label>
                            <div class="location-autocomplete" data-location-autocomplete>
                                <input class="form-control @error('moving_from') is-invalid @enderror" id="pickup_location" name="moving_from"
                                    type="text" value="{{ old('moving_from', $quote->moving_from) }}" placeholder="Start typing a Kenyan pickup area"
                                    autocomplete="off" data-location-next="#dropoff_location" required>
                                <div class="location-autocomplete__menu" id="pickup_location_suggestions" role="listbox" aria-label="Pickup location suggestions"></div>
                            </div>
                            @error('moving_from')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="dropoff_location">Drop-off Location</label>
                            <div class="location-autocomplete" data-location-autocomplete>
                                <input class="form-control @error('moving_to') is-invalid @enderror" id="dropoff_location" name="moving_to"
                                    type="text" value="{{ old('moving_to', $quote->moving_to) }}" placeholder="Start typing a Kenyan drop-off area"
                                    autocomplete="off" data-location-next="#service_type" required>
                                <div class="location-autocomplete__menu" id="dropoff_location_suggestions" role="listbox" aria-label="Drop-off location suggestions"></div>
                            </div>
                            @error('moving_to')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="service_type">Service Type</label>
                            <select class="form-control @error('service_type') is-invalid @enderror" id="service_type" name="service_type" required>
                                <option value="" @selected($selectedServiceType === '')>Select Service Type</option>
                                @foreach ($serviceTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedServiceType === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="move_size_wrapper">
                            <label class="form-label" for="move_size">Move Size</label>
                            <!-- Residential Relocation Size Dropdown -->
                            <select class="form-control @error('move_size') is-invalid @enderror" id="move_size_residential" name="move_size" style="display: block;">
                                <option value="">Select Bedroom Type</option>
                                <option value="Studio" @if(old('move_size', $quote->move_size) === 'Studio') selected @endif>Studio</option>
                                <option value="Bedsitter" @if(old('move_size', $quote->move_size) === 'Bedsitter') selected @endif>Bedsitter</option>
                                <option value="1 Bedroom" @if(old('move_size', $quote->move_size) === '1 Bedroom') selected @endif>1 Bedroom</option>
                                <option value="2 Bedroom" @if(old('move_size', $quote->move_size) === '2 Bedroom') selected @endif>2 Bedroom</option>
                                <option value="3 Bedroom" @if(old('move_size', $quote->move_size) === '3 Bedroom') selected @endif>3 Bedroom</option>
                                <option value="4 Bedroom" @if(old('move_size', $quote->move_size) === '4 Bedroom') selected @endif>4 Bedroom</option>
                                <option value="5 Bedroom" @if(old('move_size', $quote->move_size) === '5 Bedroom') selected @endif>5 Bedroom</option>
                                <option value="Villa" @if(old('move_size', $quote->move_size) === 'Villa') selected @endif>Villa</option>
                                <option value="Bungalow" @if(old('move_size', $quote->move_size) === 'Bungalow') selected @endif>Bungalow</option>
                            </select>
                            <!-- Office Relocation Size Text Input -->
                            <input class="form-control @error('move_size') is-invalid @enderror" id="move_size_office" name="move_size" placeholder="Office size in square feet or description" type="text" value="{{ old('move_size', $quote->move_size) }}" style="display: none;">
                            @error('move_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="additional_notes">Additional Notes</label>
                            <textarea class="form-control @error('additional_notes') is-invalid @enderror" id="additional_notes" name="additional_notes"
                                rows="5">{{ old('additional_notes', $quote->additional_notes) }}</textarea>
                            @error('additional_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Quote Status</h5>

                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($isEditing)
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a class="btn btn-sm btn-primary" href="{{ route('quotes.show', $quote) }}">View Details</a>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success" type="submit">{{ $isEditing ? 'Update Quote' : 'Save Quote' }}</button>
                        <a class="btn btn-outline-secondary" href="{{ $isEditing ? route('quotes.show', $quote) : route('quotes.index') }}">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Submission Metadata</h5>

                    <div class="mb-3">
                        <label class="form-label" for="source_page">Source Page</label>
                        <input class="form-control @error('source_page') is-invalid @enderror" id="source_page" name="source_page"
                            type="text" value="{{ old('source_page', $quote->source_page) }}">
                        @error('source_page')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="ip_address">IP Address</label>
                        <input class="form-control @error('ip_address') is-invalid @enderror" id="ip_address" name="ip_address"
                            type="text" value="{{ old('ip_address', $quote->ip_address) }}">
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label" for="user_agent">User Agent</label>
                        <textarea class="form-control @error('user_agent') is-invalid @enderror" id="user_agent" name="user_agent" readonly
                            rows="4">{{ old('user_agent', $quote->user_agent) }}</textarea>
                        @error('user_agent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@if ($canDeleteQuote)
    <div class="row">
        <div class="col-xl-4 ms-auto">
            <div class="card border-danger border-opacity-25">
                <div class="card-body">
                    <h5 class="card-title text-danger mb-2">Delete Quote</h5>
                    <p class="text-muted mb-3">Remove this quote permanently.</p>
                    <form action="{{ route('quotes.destroy', $quote) }}" data-delete-confirm data-delete-message="Do you want to delete this quote?" data-delete-title="Delete quote?" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger" type="submit">Delete Quote</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener("DOMContentLoaded", function() {
    const serviceTypeSelect = document.getElementById("service_type");
    const moveResidentialSelect = document.getElementById("move_size_residential");
    const moveOfficeInput = document.getElementById("move_size_office");

    if (!serviceTypeSelect || !moveResidentialSelect || !moveOfficeInput) {
        return;
    }

    function updateMoveSizeField() {
        const serviceType = serviceTypeSelect.value;
        if (serviceType === "Residential Relocation") {
            moveResidentialSelect.style.display = "block";
            moveOfficeInput.style.display = "none";
            moveResidentialSelect.name = "move_size";
            moveOfficeInput.name = "";
        } else if (serviceType === "Office Relocation") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Office size in square feet or description";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else if (serviceType === "Long-Distance Move") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Distance or route details";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else if (serviceType === "Packing & Storage") {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "block";
            moveOfficeInput.placeholder = "Storage details or item description";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "move_size";
        } else {
            moveResidentialSelect.style.display = "none";
            moveOfficeInput.style.display = "none";
            moveResidentialSelect.name = "";
            moveOfficeInput.name = "";
        }
    }

    serviceTypeSelect.addEventListener("change", updateMoveSizeField);
    updateMoveSizeField();
});
</script>
@endsection
