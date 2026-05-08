@extends('layouts.vertical', ['title' => 'Message - ' . $message->subject])

@php
    $replySubject = Str::startsWith(Str::lower($message->subject), 're:')
        ? $message->subject
        : 'Re: ' . $message->subject;
    $deliveryLog = $message->latestEmailLog;
@endphp

@section('content')
<div class="row" data-message-detail data-index-url="{{ route('messages.index') }}">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h5 class="card-title mb-1">{{ $message->subject }}</h5>
                        <small class="text-muted">From {{ $message->name }} ({{ $message->email }})</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
                        </a>
                        <a href="#reply-card" class="btn btn-sm btn-primary">
                            <i data-lucide="reply" class="icon-sm me-1"></i>Reply
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-message-delete-detail data-delete-url="{{ route('messages.destroy', $message) }}">
                            <i data-lucide="trash-2" class="icon-sm me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Sender</h6>
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
                                <p class="fw-medium">{{ $message->phone }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <p class="fw-medium">{{ $message->created_at->format('d M Y h:i A') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <p>
                                @if($message->status === 'responded')
                                    <span id="message-status-badge" class="badge bg-success">Responded</span>
                                @elseif($message->status === 'draft')
                                    <span id="message-status-badge" class="badge bg-warning">Draft</span>
                                @elseif($message->status === 'sent')
                                    <span id="message-status-badge" class="badge bg-info">Sent</span>
                                @elseif($message->status === 'read')
                                    <span id="message-status-badge" class="badge bg-secondary">Read</span>
                                @else
                                    <span id="message-status-badge" class="badge bg-danger">Unread</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery</label>
                            <p>
                                @if($deliveryLog?->status === \App\Models\EmailLog::STATUS_SENT)
                                    <span class="badge bg-success">✅ Sent</span>
                                @elseif($deliveryLog?->status === \App\Models\EmailLog::STATUS_OPENED)
                                    <span class="badge bg-info">👁 Opened</span>
                                @elseif($deliveryLog?->status === \App\Models\EmailLog::STATUS_FAILED)
                                    <span class="badge bg-danger">❌ Failed</span>
                                @elseif($deliveryLog)
                                    <span class="badge bg-warning">Sending</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <p><span class="badge badge-soft-{{ $message->categoryBadgeClass() }}">{{ $message->categoryLabel() }}</span></p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-4">
                    <h6 class="text-muted mb-2">Message</h6>
                    <div class="border rounded p-3 bg-light" style="min-height: 150px; white-space: pre-wrap;">{{ $message->message }}</div>
                </div>

                @if($message->attachment_original_name)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Attachment</h6>
                        <span class="badge badge-soft-secondary">{{ $message->attachment_original_name }}</span>
                    </div>
                @endif

                <div id="response-preview" class="{{ $message->response ? '' : 'd-none' }}">
                    <hr>
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Your Response</h6>
                        <div id="latest-response-body" class="border rounded p-3" style="background-color: #f0f7ff; white-space: pre-wrap;">{{ $message->response }}</div>
                        <small class="text-muted d-block mt-2" id="latest-response-meta">
                            @if($message->responded_at)
                                Responded on {{ $message->responded_at->format('d M Y h:i A') }}
                                @if($message->respondedByUser)
                                    by {{ $message->respondedByUser->name }}
                                @endif
                            @endif
                        </small>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 border-top pt-3">
                    <a href="#reply-card" class="btn btn-primary">
                        <i data-lucide="reply" class="icon-sm me-1"></i>Reply
                    </a>
                    <button type="button" class="btn btn-outline-danger" data-message-delete-detail data-delete-url="{{ route('messages.destroy', $message) }}">
                        <i data-lucide="trash-2" class="icon-sm me-1"></i>Delete
                    </button>
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary ms-auto">Back to Inbox</a>
                </div>
            </div>
        </div>

        <div class="card mt-3" id="reply-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Reply</h5>
            </div>
            <div class="card-body">
                <form id="message-reply-form" method="POST" action="{{ route('messages.respond', $message) }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="recipient_email">To</label>
                        <input type="email" id="recipient_email" name="recipient_email" class="form-control" value="{{ $message->email }}" required>
                        <div class="invalid-feedback" data-error-for="recipient_email"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="reply_subject">Subject</label>
                        <input type="text" id="reply_subject" name="subject" class="form-control" value="{{ $replySubject }}" maxlength="255" required>
                        <div class="invalid-feedback" data-error-for="subject"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="response">Message</label>
                        <textarea id="response" name="response" class="form-control" rows="7" required></textarea>
                        <div class="invalid-feedback" data-error-for="response"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Original Message</label>
                        <blockquote class="border-start border-3 ps-3 text-muted mb-0" style="white-space: pre-wrap;">{{ $message->message }}</blockquote>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-reply-button>
                            <i data-lucide="send" class="icon-sm me-1"></i>Send Reply
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const detail = document.querySelector('[data-message-detail]');
        const replyForm = document.getElementById('message-reply-form');
        const replyButton = replyForm?.querySelector('[data-reply-button]');

        function clearErrors(form) {
            form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
            form.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function showErrors(form, errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error-for="${field}"]`);

                if (input) {
                    input.classList.add('is-invalid');
                }

                if (error) {
                    error.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });
        }

        document.querySelectorAll('[data-message-delete-detail]').forEach((button) => {
            button.addEventListener('click', async function () {
                const deleteUrl = this.dataset.deleteUrl;
                this.disabled = true;

                try {
                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.error || 'Delete failed');
                    }

                    showToast('Message deleted', 'success');
                    setTimeout(() => {
                        window.location.href = detail?.dataset.indexUrl || '{{ route('messages.index') }}';
                    }, 500);
                } catch (error) {
                    this.disabled = false;
                    showToast('Failed to delete message', 'error');
                }
            });
        });

        if (replyForm && replyButton) {
            replyForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                clearErrors(replyForm);
                replyButton.disabled = true;
                replyButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Sending';

                try {
                    const response = await fetch(replyForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(replyForm),
                    });
                    
                    let data;
                    const contentType = response.headers.get('content-type');
                    
                    if (!contentType || !contentType.includes('application/json')) {
                        const responseText = await response.text();
                        console.error('Server returned non-JSON response:', responseText);
                        throw new Error('Server error: Invalid response format. Please check your SMTP configuration and try again.');
                    }
                    
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        const responseText = await response.clone().text();
                        console.error('Failed to parse JSON response:', parseError, 'Response text:', responseText);
                        throw new Error('Server error: Invalid JSON response. Please contact support.');
                    }

                    if (!response.ok || !data.success) {
                        if (data.errors) {
                            showErrors(replyForm, data.errors);
                        }

                        throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Reply could not be sent.');
                    }

                    const responsePreview = document.getElementById('response-preview');
                    const responseBody = document.getElementById('latest-response-body');
                    const responseMeta = document.getElementById('latest-response-meta');
                    const statusBadge = document.getElementById('message-status-badge');

                    if (responsePreview) {
                        responsePreview.classList.remove('d-none');
                    }

                    if (responseBody) {
                        responseBody.textContent = replyForm.querySelector('[name="response"]').value;
                    }

                    if (responseMeta) {
                        responseMeta.textContent = 'Responded just now';
                    }

                    if (statusBadge) {
                        statusBadge.className = 'badge bg-success';
                        statusBadge.textContent = 'Responded';
                    }

                    replyForm.querySelector('[name="response"]').value = '';
                    showToast('Reply sent successfully', 'success');
                } catch (error) {
                    showToast('Failed to send reply: ' + error.message, 'error');
                } finally {
                    replyButton.disabled = false;
                    replyButton.innerHTML = '<i data-lucide="send" class="icon-sm me-1"></i>Send Reply';

                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }
            });
        }
    });
</script>
@endsection
