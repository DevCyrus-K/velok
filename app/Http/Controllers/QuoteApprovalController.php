<?php

namespace App\Http\Controllers;

use App\Mail\QuoteApprovedAdminMail;
use App\Mail\QuoteApprovedCustomerMail;
use App\Models\EmailLog;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Services\ServiceAgreementService;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\NotificationLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class QuoteApprovalController extends Controller
{
    public function show(string $token): View
    {
        $quotation = Quotation::query()
            ->with('quoteRequest')
            ->where('approval_token', $token)
            ->firstOrFail();

        if ($quotation->approval_token_expires_at && now()->isAfter($quotation->approval_token_expires_at)) {
            $company = app(CompanyProfile::class)->data();

            return view('quotes.approval-expired', [
                'companyPhone' => $company['phone'] ?? null,
                'companyEmail' => $company['email'] ?? null,
            ]);
        }

        if ($quotation->status === Quotation::STATUS_APPROVED) {
            return view('quotes.already-approved', compact('quotation'));
        }

        $quotation->logStage(
            'APPROVAL_LINK_CLICKED',
            'Customer clicked the approval link',
            'customer',
            $quotation->customer_name,
            request()->ip(),
            'online'
        );

        return view('quotes.approve', [
            'quote' => $quotation,
            'quotation' => $quotation,
            'paymentMethods' => app(BookingFlow::class)->paymentMethodDisplays(),
            'company' => app(CompanyProfile::class)->data(),
        ]);
    }

    public function approve(Request $request, string $token, ServiceAgreementService $serviceAgreements): RedirectResponse
    {
        $quotation = Quotation::query()
            ->with('quoteRequest')
            ->where('approval_token', $token)
            ->firstOrFail();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'agreement' => ['required', 'accepted'],
        ]);

        if ($quotation->status === Quotation::STATUS_APPROVED) {
            return redirect()->route('quote.approval.thankyou', $quotation);
        }

        if ($quotation->approval_token_expires_at && now()->isAfter($quotation->approval_token_expires_at)) {
            return back()->withErrors([
                'token' => 'This approval link has expired.',
            ]);
        }

        DB::transaction(function () use ($quotation, $validated, $request): void {
            $quotation->update([
                'status' => Quotation::STATUS_APPROVED,
                'approval_date' => now()->toDateString(),
                'approved_by_name' => $validated['full_name'],
                'approval_ip' => $request->ip(),
                'approval_method' => 'Online - '.($quotation->sent_via ?? 'Link'),
                'approval_token' => null,
            ]);

            $quotation->quoteRequest?->updateQuietly([
                'status' => QuoteRequest::STATUS_CREATED,
                'approval_date' => $quotation->quoteRequest?->approval_date ?: now()->toDateString(),
            ]);

            $quotation->logStage(
                'APPROVED_ONLINE',
                "Quotation approved online by {$validated['full_name']}",
                'customer',
                $validated['full_name'],
                $request->ip(),
                'online',
                ['agreement' => true]
            );

            $quotation->logStage(
                'DEPOSIT_PENDING',
                'Deposit of KES '.number_format($quotation->depositAmount(), 2).' required to confirm booking',
                'system'
            );
        });

        $quotation->refresh()->loadMissing('quoteRequest');
        app(NotificationLogger::class)->quoteApprovedByClient($quotation);

        try {
            $serviceAgreements->generateAndSendForApprovedQuotation($quotation);
        } catch (Throwable $exception) {
            // Production hardening: client approval continues while storage/mail failures are logged.
            Log::error('Service agreement generation failed after client approval', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        $adminEmail = $this->adminEmail();
        $clientName = trim((string) ($quotation->approved_by_name ?: $quotation->customer_name)) ?: 'Client';

        if ($adminEmail) {
            $this->sendLoggedQuoteApprovalEmail(
                $quotation,
                $adminEmail,
                'Quote approved by '.$clientName,
                fn (?string $trackingToken) => new QuoteApprovedAdminMail($quotation, $trackingToken)
            );
        }

        if ($quotation->contact_preference !== 'whatsapp') {
            $this->sendLoggedQuoteApprovalEmail(
                $quotation,
                $quotation->customer_email,
                'Your quotation is approved '.$quotation->reference,
                fn (?string $trackingToken) => new QuoteApprovedCustomerMail($quotation, $trackingToken)
            );
        }

        return redirect()->route('quote.approval.thankyou', $quotation);
    }

    public function thankyou(Quotation $quotation): View
    {
        $quotation->loadMissing('quoteRequest');

        return view('quotes.approval-thankyou', [
            'quotation' => $quotation,
            'company' => app(CompanyProfile::class)->data(),
            'paymentMethods' => app(BookingFlow::class)->paymentMethodDisplays(),
        ]);
    }

    private function adminEmail(): ?string
    {
        $company = app(CompanyProfile::class)->data();
        $email = trim((string) ($company['email'] ?? config('mail.from.address')));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function sendLoggedQuoteApprovalEmail(Quotation $quotation, ?string $recipient, string $subject, callable $mailFactory): void
    {
        $recipient = Str::lower(trim((string) $recipient));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $emailLog = $this->createEmailLog($quotation, $recipient, $subject);

        try {
            Mail::to($recipient)->send($mailFactory($emailLog?->tracking_token));
            $this->markEmailLogSent($emailLog);
        } catch (Throwable $exception) {
            $this->markEmailLogFailed($emailLog, $exception);
            Log::error('Quote approval notification failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    private function createEmailLog(Quotation $quotation, string $recipient, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return $quotation->emailLogs()->create([
                'recipient_email' => Str::limit($recipient, 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Quote approval email log creation failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return null;
        }
    }

    private function markEmailLogSent(?EmailLog $emailLog): void
    {
        if (! $emailLog) {
            return;
        }

        $emailLog->increment('attempts');
        $emailLog->update([
            'status' => EmailLog::STATUS_QUEUED,
            'sent_at' => null,
            'failed_reason' => null,
        ]);
    }

    private function markEmailLogFailed(?EmailLog $emailLog, Throwable $exception): void
    {
        if (! $emailLog) {
            return;
        }

        $emailLog->update([
            'status' => EmailLog::STATUS_FAILED,
            'failed_reason' => Str::limit($exception->getMessage(), 1000, ''),
            'attempts' => $emailLog->attempts + 1,
        ]);
    }
}
