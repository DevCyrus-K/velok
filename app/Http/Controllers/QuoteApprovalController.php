<?php

namespace App\Http\Controllers;

use App\Mail\QuoteApprovedAdminMail;
use App\Mail\QuoteApprovedCustomerMail;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

    public function approve(Request $request, string $token): RedirectResponse
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

            $quotation->quoteRequest?->update([
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

        $adminEmail = $this->adminEmail();

        try {
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new QuoteApprovedAdminMail($quotation));
            }

            if ($quotation->contact_preference !== 'whatsapp') {
                Mail::to($quotation->customer_email)->send(new QuoteApprovedCustomerMail($quotation));
            }
        } catch (Throwable $exception) {
            report($exception);
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
}
