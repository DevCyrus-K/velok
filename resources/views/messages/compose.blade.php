@extends('layouts.vertical', ['title' => 'Compose Message'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Compose New Message</h5>
                    <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="x" class="icon-sm me-1"></i>Close
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('messages.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Recipient's name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="recipient@example.com" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone (Optional)</label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+254 123 456 789" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="origin_page" class="form-control @error('origin_page') is-invalid @enderror" required>
                                    <option value="">-- Select Message Type --</option>
                                    <option value="general" {{ old('origin_page') === 'general' ? 'selected' : '' }}>General Message</option>
                                    <option value="quote" {{ old('origin_page') === 'quote' ? 'selected' : '' }}>Quote Follow-up</option>
                                    <option value="invoice" {{ old('origin_page') === 'invoice' ? 'selected' : '' }}>Invoice Related</option>
                                    <option value="feedback" {{ old('origin_page') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                                    <option value="other" {{ old('origin_page') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('origin_page')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="Message subject" value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="8" placeholder="Type your message here..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">
                            <i data-lucide="info" class="icon-sm me-1"></i>
                            This message will be logged in the system and marked as sent.
                        </small>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" name="action" value="send" class="btn btn-primary">
                            <i data-lucide="send" class="icon-sm me-1"></i>Send Message
                        </button>
                        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                            <i data-lucide="save" class="icon-sm me-1"></i>Save as Draft
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary ms-auto">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
