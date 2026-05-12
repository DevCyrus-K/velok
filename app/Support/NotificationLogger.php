<?php

namespace App\Support;

use App\Models\ActivityNotification;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class NotificationLogger
{
    public function log(array $data, ?Model $related = null): ?ActivityNotification
    {
        if (! $this->canLog()) {
            return null;
        }

        try {
            $payload = [
                'type' => Str::limit((string) ($data['type'] ?? 'activity'), 80, ''),
                'title' => Str::limit((string) ($data['title'] ?? 'New activity'), 190, ''),
                'body' => filled($data['body'] ?? null) ? Str::limit((string) $data['body'], 1000, '') : null,
                'url' => filled($data['url'] ?? null) ? Str::limit((string) $data['url'], 255, '') : null,
                'icon' => Str::limit((string) ($data['icon'] ?? 'bell'), 80, ''),
                'severity' => Str::limit((string) ($data['severity'] ?? 'info'), 40, ''),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'metadata' => $data['metadata'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
            ];

            if ($related) {
                $payload['related_type'] = $related::class;
                $payload['related_id'] = $related->getKey();
            }

            $notification = ActivityNotification::query()->create($payload);
            app(TopbarData::class)->forgetNotifications();

            return $notification;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public function messageReceived(Message $message): void
    {
        $this->log([
            'type' => 'message_received',
            'title' => 'New message from '.$message->name,
            'body' => $message->subject,
            'url' => route('messages.show', $message),
            'icon' => 'mail',
            'severity' => 'primary',
        ], $message);
    }

    public function messageSent(Message $message, string $recipient, string $subject): void
    {
        $this->log([
            'type' => 'message_sent',
            'title' => 'Message sent',
            'body' => $subject.' to '.$recipient,
            'url' => route('messages.show', $message),
            'icon' => 'send',
            'severity' => 'success',
        ], $message);
    }

    public function messageFailed(Message $message, string $recipient, string $reason): void
    {
        $this->log([
            'type' => 'message_failed',
            'title' => 'Message delivery failed',
            'body' => $recipient.': '.$reason,
            'url' => route('messages.show', $message),
            'icon' => 'triangle-alert',
            'severity' => 'danger',
        ], $message);
    }

    public function careerApplicationReceived(JobApplication $application): void
    {
        $this->log([
            'type' => 'career_application',
            'title' => 'New career application',
            'body' => $application->applicant_name.' applied for '.$application->job_title,
            'url' => route('careers.applications.show', $application),
            'icon' => 'briefcase-business',
            'severity' => 'primary',
        ], $application);
    }

    public function careerApplicationUpdated(JobApplication $application): void
    {
        $this->log([
            'type' => 'career_application_updated',
            'title' => 'Application marked '.$application->statusLabel(),
            'body' => $application->applicant_name.' - '.$application->job_title,
            'url' => route('careers.applications.show', $application),
            'icon' => 'clipboard-check',
            'severity' => 'info',
        ], $application);
    }

    public function quoteRequestReceived(QuoteRequest $quote): void
    {
        $this->log([
            'type' => 'quote_request',
            'title' => 'New quote request',
            'body' => $quote->full_name.' - '.$quote->routeSummary(),
            'url' => route('quotes.show', $quote),
            'icon' => 'message-square-quote',
            'severity' => 'primary',
        ], $quote);
    }

    public function quoteRequestUpdated(QuoteRequest $quote): void
    {
        $this->log([
            'type' => 'quote_request_updated',
            'title' => 'Quote marked '.$quote->statusLabel(),
            'body' => $quote->full_name.' - '.$quote->reference(),
            'url' => route('quotes.show', $quote),
            'icon' => 'message-square-quote',
            'severity' => 'info',
        ], $quote);
    }

    public function quoteApprovedByClient(Quotation $quotation): void
    {
        $quotation->loadMissing('quoteRequest');

        $clientName = trim((string) ($quotation->approved_by_name ?: $quotation->customer_name));
        $clientName = $clientName !== '' ? $clientName : 'Client';
        $quote = $quotation->quoteRequest;

        $this->log([
            'type' => 'quote_approved',
            'title' => 'Quote approved by '.$clientName,
            'body' => $quotation->reference,
            'url' => $quote ? route('quotes.show', $quote) : route('quotations.show', $quotation),
            'icon' => 'badge-check',
            'severity' => 'success',
        ], $quote ?: $quotation);
    }

    public function serviceAgreementEmailFailed(Quotation $quotation, string $recipient, string $reason): void
    {
        $quotation->loadMissing('quoteRequest');
        $quote = $quotation->quoteRequest;

        $this->log([
            'type' => 'service_agreement_email_failed',
            'title' => 'Service agreement email failed',
            'body' => Str::limit($quotation->reference.' to '.$recipient.': '.$reason, 1000, ''),
            'url' => $quote ? route('quotes.show', $quote) : route('quotations.show', $quotation),
            'icon' => 'triangle-alert',
            'severity' => 'danger',
        ], $quote ?: $quotation);
    }

    public function loginSucceeded(User $user, Request $request): void
    {
        $this->log([
            'type' => 'login_success',
            'title' => 'Login successful',
            'body' => $user->email.' from '.($request->ip() ?: 'unknown IP'),
            'url' => route('account.show'),
            'icon' => 'log-in',
            'severity' => 'success',
            'metadata' => $this->requestMetadata($request),
        ], $user);
    }

    public function loginFailed(string $email, Request $request): void
    {
        $email = Str::lower(trim($email));
        $user = $email !== ''
            ? User::query()->whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        $this->log([
            'type' => 'login_failed',
            'title' => 'Failed login attempt',
            'body' => ($email !== '' ? $email : 'Unknown email').' from '.($request->ip() ?: 'unknown IP'),
            'url' => route('login'),
            'icon' => 'shield-alert',
            'severity' => 'warning',
            'metadata' => $this->requestMetadata($request),
        ], $user);
    }

    public function passwordChanged(User $user, Request $request): void
    {
        $this->log([
            'type' => 'password_changed',
            'title' => 'Password changed',
            'body' => $user->email.' updated account security.',
            'url' => route('account.show'),
            'icon' => 'key-round',
            'severity' => 'warning',
            'metadata' => $this->requestMetadata($request),
        ], $user);
    }

    public function accountActivity(User $user, string $title, string $body, string $icon = 'circle-user'): void
    {
        $this->log([
            'type' => 'account_activity',
            'title' => $title,
            'body' => $body,
            'url' => route('account.show'),
            'icon' => $icon,
            'severity' => 'info',
        ], $user);
    }

    public function markReadFor(Model $related): void
    {
        if (! $this->canLog()) {
            return;
        }

        try {
            ActivityNotification::query()
                ->where('related_type', $related::class)
                ->where('related_id', $related->getKey())
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'updated_at' => now()]);

            app(TopbarData::class)->forgetNotifications();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function canLog(): bool
    {
        try {
            return Schema::hasTable('activity_notifications');
        } catch (Throwable) {
            return false;
        }
    }

    private function requestMetadata(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ];
    }
}
