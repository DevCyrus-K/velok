<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use App\Support\LeadCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(): View
    {
        $quotes = QuoteRequest::query()
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
                'pending' => $quotes->whereIn('status', ['new', 'processing', 'emailed', 'email_failed'])->count(),
                'approved' => $quotes->where('status', 'quoted')->count(),
                'declined' => $quotes->whereIn('status', ['closed', 'spam'])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('quotes.form', $this->formViewData(
            new QuoteRequest([
                'status' => 'new',
                'source_page' => '/admin/quotes',
            ])
        ));
    }

    public function store(HttpRequest $request): RedirectResponse
    {
        $quote = QuoteRequest::query()->create($this->validatedData($request));

        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-success', 'Quote created successfully.');
    }

    public function show(QuoteRequest $quote): View
    {
        $quote->load('quotation');

        return view('quotes.show', [
            'quote' => $quote,
            'quotation' => $quote->quotation,
        ]);
    }

    public function edit(QuoteRequest $quote): View
    {
        return view('quotes.form', $this->formViewData($quote));
    }

    public function update(HttpRequest $request, QuoteRequest $quote): RedirectResponse
    {
        $quote->update($this->validatedData($request, $quote));

        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-success', 'Quote updated successfully.');
    }

    public function destroy(QuoteRequest $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()
            ->route('quotes.index')
            ->with('toast-success', 'Quote deleted successfully.');
    }

    public function approve(QuoteRequest $quote): RedirectResponse
    {
        $quote->update(['status' => 'quoted']);

        return back()->with('toast-success', 'Quote approved successfully.');
    }

    public function decline(QuoteRequest $quote): RedirectResponse
    {
        $quote->update(['status' => 'closed']);

        return back()->with('toast-success', 'Quote declined successfully.');
    }

    private function formViewData(QuoteRequest $quote): array
    {
        return [
            'quote' => $quote,
            'isEditing' => $quote->exists,
            'statusOptions' => QuoteRequest::statusOptions(),
            'serviceTypes' => QuoteRequest::query()
                ->select('service_type')
                ->distinct()
                ->orderBy('service_type')
                ->pluck('service_type')
                ->filter()
                ->map(fn (?string $serviceType) => LeadCategory::serviceTypeLabel($serviceType))
                ->unique()
                ->sort()
                ->values(),
        ];
    }

    private function validatedData(HttpRequest $request, ?QuoteRequest $quote = null): array
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'moving_from' => ['required', 'string', 'max:190'],
            'moving_to' => ['required', 'string', 'max:190'],
            'move_date' => ['nullable', 'date'],
            'service_type' => ['required', 'string', 'max:120'],
            'move_size' => ['nullable', 'string', 'max:160'],
            'additional_notes' => ['nullable', 'string'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(QuoteRequest::statusOptions()))],
        ]);

        $defaultSourcePage = $quote?->source_page ?: '/admin/quotes';
        $defaultIpAddress = $quote?->ip_address ?: $request->ip();
        $defaultUserAgent = $quote?->user_agent ?: Str::limit((string) $request->userAgent(), 255, '');

        return [
            'full_name' => $this->squish($validated['full_name']),
            'email' => Str::lower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'moving_from' => $this->squish($validated['moving_from']),
            'moving_to' => $this->squish($validated['moving_to']),
            'move_date' => $validated['move_date'] ?: null,
            'service_type' => LeadCategory::normalizeServiceType($validated['service_type']) ?? $this->squish($validated['service_type']),
            'move_size' => $this->nullableSquish($validated['move_size'] ?? null),
            'additional_notes' => $this->nullableTrim($validated['additional_notes'] ?? null),
            'source_page' => $this->nullableTrim($validated['source_page'] ?? null) ?: $defaultSourcePage,
            'ip_address' => $this->nullableTrim($validated['ip_address'] ?? null) ?: $defaultIpAddress,
            'user_agent' => $this->nullableTrim($validated['user_agent'] ?? null) ?: $defaultUserAgent,
            'status' => $validated['status'],
        ];
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function nullableSquish(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $this->squish($value);
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
