@extends('layouts.vertical', ['title' => 'Compose Email'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0">Compose Email</h5>
                    <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="x" class="icon-sm me-1"></i>Close
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="message-compose-form" method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">To <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="recipient@example.com" required>
                        <div class="invalid-feedback" data-error-for="email"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="sender_role">Send From</label>
                        <select class="form-select" id="sender_role" name="sender_role">
                            @foreach($messageSenders as $sender)
                                <option value="{{ $sender['role'] }}" @selected($sender['role'] === \App\Support\MailSender::INFO)>
                                    {{ $sender['label'] }} - {{ $sender['address'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-error-for="sender_role"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="subject">Subject <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback" data-error-for="subject"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="message">Message <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="9" required></textarea>
                        <div class="invalid-feedback" data-error-for="message"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment">Attach File</label>
                        <input type="file" id="attachment" name="attachment" class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png,.txt,.csv,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Optional. PDF, image, text, CSV, Word, or Excel files up to 10MB.</div>
                        <div class="invalid-feedback" data-error-for="attachment"></div>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary" data-send-button>
                            <i data-lucide="send" class="icon-sm me-1"></i>Send
                        </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary ms-auto">Cancel</a>
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
        const form = document.getElementById('message-compose-form');

        if (!form) {
            return;
        }

        const csrfToken = form.querySelector('input[name="_token"]').value;
        const sendButton = form.querySelector('[data-send-button]');

        function clearErrors() {
            form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
            form.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function showErrors(errors) {
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

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearErrors();
            sendButton.disabled = true;
            sendButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Sending';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                
                let data;
                try {
                    data = await response.json();
                } catch (parseError) {
                    console.error('Failed to parse response:', parseError);
                    throw new Error('Invalid server response. Please check your internet connection and try again.');
                }

                if (!response.ok || !data.success) {
                    if (data.errors) {
                        showErrors(data.errors);
                    }

                    throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Email could not be sent.');
                }

                form.reset();
                showToast('Email sent successfully', 'success');
            } catch (error) {
                showToast('Failed to send: ' + error.message, 'error');
            } finally {
                sendButton.disabled = false;
                sendButton.innerHTML = '<i data-lucide="send" class="icon-sm me-1"></i>Send';

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }
        });
    });
</script>
@endsection
