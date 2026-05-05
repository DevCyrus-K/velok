@extends('layouts.vertical', ['title' => 'Message - ' . $message->subject])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">{{ $message->subject }}</h5>
                        <small class="text-muted">From {{ $message->name }} ({{ $message->email }})</small>
                    </div>
                    <div>
                        <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Message Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Sender Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <p class="fw-medium">{{ $message->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <p class="fw-medium"><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p>
                        </div>
                        @if($message->phone)
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <p class="fw-medium"><a href="tel:{{ $message->phone }}">{{ $message->phone }}</a></p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Message Info</h6>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <p class="fw-medium">{{ $message->created_at->format('d M Y H:i A') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <p>
                                @if($message->status === 'responded')
                                    <span class="badge bg-success">Responded</span>
                                @elseif($message->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @elseif($message->status === 'sent')
                                    <span class="badge bg-info">Sent</span>
                                @elseif($message->status === 'read')
                                    <span class="badge bg-secondary">Read</span>
                                @else
                                    <span class="badge bg-danger">Unread</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <p><span class="badge badge-soft-{{ $message->categoryBadgeClass() }}">{{ $message->categoryLabel() }}</span></p>
                        </div>
                        @if($message->origin_page)
                            <div class="mb-3">
                                <label class="form-label">From Page</label>
                                <p class="fw-medium">{{ $message->origin_page }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <hr>

                <!-- Message Content -->
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Message</h6>
                    <div class="border rounded p-3" style="background-color: #f8f9fa; min-height: 150px;">
                        {{ $message->message }}
                    </div>
                </div>

                <!-- Previous Response (if exists) -->
                @if($message->response)
                    <hr>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Your Response</h6>
                        <div class="border rounded p-3" style="background-color: #f0f7ff;">
                            {{ $message->response }}
                        </div>
                        <small class="text-muted d-block mt-2">
                            Responded on {{ $message->responded_at->format('d M Y H:i A') }}
                            @if($message->respondedByUser)
                                by {{ $message->respondedByUser->name }}
                            @endif
                        </small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Response Form -->
        @if($message->status !== 'responded')
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Respond to Message</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('messages.respond', $message) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Your Response</label>
                            <textarea name="response" class="form-control @error('response') is-invalid @enderror" rows="5" placeholder="Type your response here..." required></textarea>
                            @error('response')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email" value="1" checked>
                            <label class="form-check-label" for="sendEmail">
                                Send this response to {{ $message->email }}
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="send" class="icon-sm me-1"></i>Send Response
                            </button>
                            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card mt-3">
                <div class="card-body text-center py-4">
                    <i data-lucide="check-circle" class="icon-lg text-success mb-2"></i>
                    <p class="text-muted">This message has been responded to.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
