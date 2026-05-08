<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequestFormRequest;
use App\Models\QuoteRequest;
use App\Support\CompanyProfile;
use App\Support\QuotationEmail;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class QuoteController extends Controller
{
    public function index(): View
    {
        $quotes = QuoteRequest::query()
            ->with(['quote', 'quotation', 'invoice'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('quotes.index', [
            'quotes' => $quotes,
            'serviceFilters' => $quotes->map(fn (QuoteRequest $quote) => $quote->serviceTypeLabel())
                ->unique()
                ->sort()
                ->values(),
            'summary' => [
                'total' => $quotes->count(),
                'pending' => $quotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'pending')->count(),
                'approved' => $quotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'approved')->count(),
                'declined' => $quotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'declined')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('quotes.form', $this->formViewData(
            new QuoteRequest([
                'status' => QuoteRequest::STATUS_NEW,
                'source_page' => '/admin/quotes',
            ])
        ));
    }

    public function store(QuoteRequestFormRequest $request): RedirectResponse
    {
        $quote = QuoteRequest::query()->create($request->quoteData());

        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-success', 'Quote created successfully.');
    }

    public function show(QuoteRequest $quote): View
    {
        $quote->load(['quote', 'quotation', 'invoice']);

        return view('quotes.show', [
            'quote' => $quote,
            'quotation' => $quote->quote,
            'invoice' => $quote->invoice,
        ]);
    }

    public function download(QuoteRequest $quote)
    {
        $quote->load('quotation');
        $quotation = $quote->quotation;
        $user = auth()->user();
        $signaturePath = $user?->signaturePath();
        $signatureExists = app(UserSignature::class)->exists($signaturePath);

        abort_unless($quotation, 404);

        $authorization = [
            'name' => $user?->name ?: ($quotation->authorized_by ?: 'Pending'),
            'job_title' => $user?->job_title ?: ($quotation->authorized_role ?: 'Authorized Signatory'),
            'signature_path' => $signaturePath ?: $quotation->signature,
            'is_complete' => $signatureExists,
            'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
            'prompt' => 'Signature not available',
        ];

        $pdf = Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
            'quotation' => $quotation,
            'company' => app(CompanyProfile::class)->data(),
            'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
            'authorization' => $authorization,
            'signatureDataUri' => app(UserSignature::class)->dataUri($signaturePath),
            'user' => $user,
        ])->setPaper('a4');

        return $pdf->download($this->quotePdfFilename($quote));
    }

    public function send(HttpRequest $request, QuoteRequest $quote, QuotationEmail $quotationEmail): RedirectResponse|JsonResponse
    {
        $quote->load('quotation');

        if (! $quote->quotation) {
            $message = 'Create the quotation before sending it to the customer.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 404);
            }

            return back()->with('toast-error', $message);
        }

        if (! in_array($quote->quotation->status, [\App\Models\Quotation::STATUS_DRAFT, \App\Models\Quotation::STATUS_SENT], true)) {
            $message = 'Only draft or sent quotations can be emailed.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('toast-error', $message);
        }

        $payload = array_merge([
            'recipient_email' => $quote->email,
            'subject' => $quotationEmail->defaultSubject($quote->quotation),
            'message' => $quotationEmail->defaultMessage($quote->quotation, $request->user()),
            'attach_pdf' => $request->has('attach_pdf') ? $request->boolean('attach_pdf') : true,
        ], $request->only(['recipient_email', 'subject', 'message']));

        $validated = validator($payload, [
            'recipient_email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            'attach_pdf' => ['required', 'boolean'],
        ])->validate();

        try {
            $result = $quotationEmail->queue($quote->quotation, $validated, $request->user());
        } catch (Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Quotation email could not be queued. Please try again.',
                ], 500);
            }

            return back()->with('toast-error', 'Quotation email could not be queued. Please try again.');
        }

        if ($result['status'] !== 'sent') {
            $message = 'Quotation email failed. Delivery status was logged.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }

            return back()->with('toast-error', $message);
        }

        $message = 'Quotation sent successfully to '.$result['recipient_email'];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'recipient_email' => $result['recipient_email'],
                'sent_at' => $result['sent_at'],
                'sent_at_human' => $result['sent_at_human'],
                'status' => $result['status'],
                'quote_status' => $result['quote_status'],
            ]);
        }

        return redirect()
            ->route('quotations.show', $quote->quotation)
            ->with('toast-success', $message);
    }

    public function edit(QuoteRequest $quote): View
    {
        $quote->loadMissing('quotation');

        abort_if($quote->quotation, 403);

        return view('quotes.form', $this->formViewData($quote));
    }

    public function update(QuoteRequestFormRequest $request, QuoteRequest $quote): RedirectResponse
    {
        $quote->loadMissing('quotation');

        abort_if($quote->quotation, 403);

        $quote->update($request->quoteData($quote));

        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-success', 'Quote updated successfully.');
    }

    public function destroy(QuoteRequest $quote): RedirectResponse
    {
        if ($quote->statusGroup() === 'approved') {
            return back()->with('toast-error', 'Approved quote requests cannot be deleted.');
        }

        $quote->delete();

        return redirect()
            ->route('quotes.index')
            ->with('toast-success', 'Quote deleted successfully.');
    }

    public function approve(HttpRequest $request, QuoteRequest $quote): RedirectResponse|JsonResponse
    {
        $quote->load('quotation');
        $approvalDate = now()->toDateString();

        if ($quote->statusGroup() !== 'pending') {
            $message = 'Only pending quote requests can be approved.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route('quotes.show', $quote)
                ->with('toast-error', $message);
        }

        if ($quote->quotation) {
            $quote->update([
                'status' => QuoteRequest::STATUS_CREATED,
                'approval_date' => $approvalDate,
            ]);

            $quote->quotation->update([
                'approval_date' => $approvalDate,
            ]);

            if ($request->expectsJson()) {
                return $this->approvalResponse($quote->refresh(), 'Quote request approved. The quotation is already created.');
            }

            return redirect()
                ->route('quotes.show', $quote)
                ->with('toast-success', 'Quote request approved. The quotation is already created.');
        }

        $quote->update([
            'status' => QuoteRequest::STATUS_QUOTED,
            'approval_date' => $approvalDate,
        ]);

        if ($request->expectsJson()) {
            return $this->approvalResponse($quote->refresh(), 'Quote request approved. Create the quotation now.');
        }

        return redirect()
            ->route('quotations.create', $quote)
            ->with('toast-success', 'Quote request approved. Create the quotation now.');
    }

    public function decline(QuoteRequest $quote): RedirectResponse
    {
        if ($quote->statusGroup() !== 'pending') {
            return redirect()
                ->route('quotes.show', $quote)
                ->with('toast-error', 'Only pending quote requests can be rejected.');
        }

        $quote->update(['status' => QuoteRequest::STATUS_CLOSED]);

        return back()->with('toast-success', 'Quote rejected successfully.');
    }

    private function formViewData(QuoteRequest $quote): array
    {
        return [
            'quote' => $quote,
            'isEditing' => $quote->exists,
            'statusOptions' => QuoteRequest::statusOptions(),
            'serviceTypeOptions' => QuoteRequest::serviceTypeOptions(),
        ];
    }

    private function approvalResponse(QuoteRequest $quote, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $quote->status,
            'status_label' => $quote->statusLabel(),
            'status_badge_class' => $quote->statusBadgeClass(),
            'approval_date' => $quote->approval_date?->format('Y-m-d'),
            'approval_date_formatted' => $quote->approval_date?->format('d M Y'),
            'has_quotation' => (bool) $quote->quotation,
            'quotation_url' => $quote->quotation ? route('quotations.show', $quote->quotation) : null,
            'create_url' => $quote->quotation ? null : route('quotations.create', $quote),
        ]);
    }

    private function quotePdfFilename(QuoteRequest $quote): string
    {
        $quoteNumber = Str::slug($quote->reference()) ?: (string) $quote->getKey();
        $customerName = Str::slug((string) $quote->full_name) ?: 'customer';

        return "Quote-{$quoteNumber}-{$customerName}.pdf";
    }
}
