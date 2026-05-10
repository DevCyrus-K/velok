<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Support\CompanyProfile;
use App\Support\InvoiceAuthorization;
use App\Support\PaymentSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Mail\SentMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InvoiceController extends Controller
{
    public function create(Request $request): View
    {
        $quotes = QuoteRequest::query()
            ->with('quotation')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(50)
            ->get();

        $selectedQuote = null;
        $quoteId = $request->integer('quote');

        if ($quoteId > 0) {
            $selectedQuote = $quotes->firstWhere('id', $quoteId) ?? QuoteRequest::query()
                ->with('quotation')
                ->find($quoteId);

            if ($selectedQuote && ! $quotes->contains('id', $selectedQuote->id)) {
                $quotes->prepend($selectedQuote);
            }
        }

        return view('invoice.create', [
            'quotes' => $quotes,
            'selectedQuote' => $selectedQuote,
            'prefillLineItems' => $this->invoiceLineItemsForQuote($selectedQuote),
            'quoteInvoiceLineItems' => $this->quoteInvoiceLineItems($quotes),
            'nextInvoiceNumber' => $this->nextInvoiceNumber(),
            'statusOptions' => Invoice::statusOptions(),
            'paymentMethodOptions' => Invoice::paymentMethodOptions(),
            'company' => app(CompanyProfile::class)->data(),
        ]);
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json([
            'invoice_number' => $this->nextInvoiceNumber(),
        ]);
    }

    public function quote(QuoteRequest $quote): JsonResponse
    {
        $quote->loadMissing('quotation');

        return response()->json([
            'id' => $quote->id,
            'reference' => $quote->reference(),
            'customer_name' => $quote->full_name,
            'customer_email' => $quote->email,
            'customer_phone' => $quote->phone,
            'move_origin' => $quote->moving_from,
            'move_destination' => $quote->moving_to,
            'move_date' => $quote->move_date?->format('Y-m-d'),
            'move_size' => $quote->move_size,
            'service_type' => $quote->serviceTypeLabel(),
            'quote_amount' => round((float) ($quote->quotation?->quote_amount ?? 0), 2),
            'line_items' => $this->invoiceLineItemsForQuote($quote),
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        abort_if($invoice->status !== Invoice::STATUS_DRAFT, 403);

        $invoice->load(['items', 'quoteRequest.quotation']);

        $quotes = QuoteRequest::query()
            ->with('quotation')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(50)
            ->get();

        if ($invoice->quoteRequest && ! $quotes->contains('id', $invoice->quoteRequest->id)) {
            $quotes->prepend($invoice->quoteRequest);
        }

        return view('invoice.create', [
            'invoice' => $invoice,
            'isEditing' => true,
            'quotes' => $quotes,
            'selectedQuote' => $invoice->quoteRequest,
            'quoteInvoiceLineItems' => $this->quoteInvoiceLineItems($quotes),
            'nextInvoiceNumber' => $invoice->invoice_number,
            'statusOptions' => Invoice::statusOptions(),
            'paymentMethodOptions' => Invoice::paymentMethodOptions(),
            'company' => app(CompanyProfile::class)->data(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote_request_id' => ['nullable', 'integer', 'exists:quote_requests,id'],
            'invoice_number' => ['nullable', 'string', 'max:80', Rule::unique('invoices', 'invoice_number')],
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'move_origin' => ['nullable', 'string', 'max:190'],
            'move_destination' => ['nullable', 'string', 'max:190'],
            'move_date' => ['nullable', 'date'],
            'move_size' => ['nullable', 'string', 'max:160'],
            'quote_reference' => ['nullable', 'string', 'max:80'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', Rule::in(array_keys(Invoice::statusOptions()))],
            'payment_method' => ['nullable', Rule::in(array_keys(Invoice::paymentMethodOptions()))],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.description' => ['required', 'array', 'min:1'],
            'items.description.*' => ['required', 'string', 'max:255'],
            'items.quantity' => ['required', 'array', 'min:1'],
            'items.quantity.*' => ['required', 'integer', 'min:1'],
            'items.unit_price' => ['required', 'array', 'min:1'],
            'items.unit_price.*' => ['required', 'numeric', 'min:0'],
        ]);

        $quote = isset($validated['quote_request_id'])
            ? QuoteRequest::find($validated['quote_request_id'])
            : null;

        $items = $this->lineItems($validated['items']);
        $subtotal = collect($items)->sum('total');
        $tax = round((float) ($validated['tax'] ?? 0), 2);
        $invoiceNumber = $this->invoiceNumber($validated['invoice_number'] ?? null);
        $shouldSendEmail = $this->shouldSendEmailForStatus($validated['status']);

        $invoice = DB::transaction(function () use ($validated, $quote, $items, $subtotal, $tax, $invoiceNumber, $shouldSendEmail) {
            $invoiceData = [
                'invoice_number' => $invoiceNumber,
                'quote_request_id' => $quote?->id,
                'customer_name' => $this->squish($validated['customer_name']),
                'customer_email' => Str::lower(trim($validated['customer_email'])),
                'customer_phone' => trim($validated['customer_phone']),
                'move_origin' => $this->nullableSquish($validated['move_origin'] ?? null),
                'move_destination' => $this->nullableSquish($validated['move_destination'] ?? null),
                'move_date' => $validated['move_date'] ?? null,
                'move_size' => $this->nullableSquish($validated['move_size'] ?? null),
                'quote_reference' => $this->nullableTrim($validated['quote_reference'] ?? null) ?: $quote?->reference(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => round($subtotal + $tax, 2),
                'status' => $shouldSendEmail ? Invoice::STATUS_PENDING : $validated['status'],
                'payment_method' => $validated['payment_method'] ?? null,
            ];

            if (Schema::hasColumn('invoices', 'notes')) {
                $invoiceData['notes'] = $this->nullableTrim($validated['notes'] ?? null);
            }

            $invoice = Invoice::query()->create($invoiceData);
            $invoice->items()->createMany($items);

            return $invoice;
        });

        $invoice->load(['items', 'quoteRequest']);
        $emailSent = $shouldSendEmail ? $this->sendAndMarkInvoice($invoice) : null;
        $toastKey = $emailSent === false ? 'toast-error' : 'toast-success';
        $toastMessage = match ($emailSent) {
            true => 'Invoice created and emailed successfully.',
            false => 'Invoice created, but the email failed to send. Status marked as Failed.',
            default => 'Invoice created successfully.',
        };

        return redirect()
            ->route('invoice.details', ['invoice' => $invoice->id])
            ->with($toastKey, $toastMessage);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status !== Invoice::STATUS_DRAFT, 403);

        $validated = $request->validate([
            'quote_request_id' => ['nullable', 'integer', 'exists:quote_requests,id'],
            'invoice_number' => ['nullable', 'string', 'max:80', Rule::unique('invoices', 'invoice_number')->ignore($invoice)],
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'move_origin' => ['nullable', 'string', 'max:190'],
            'move_destination' => ['nullable', 'string', 'max:190'],
            'move_date' => ['nullable', 'date'],
            'move_size' => ['nullable', 'string', 'max:160'],
            'quote_reference' => ['nullable', 'string', 'max:80'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', Rule::in(array_keys(Invoice::statusOptions()))],
            'payment_method' => ['nullable', Rule::in(array_keys(Invoice::paymentMethodOptions()))],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.description' => ['required', 'array', 'min:1'],
            'items.description.*' => ['required', 'string', 'max:255'],
            'items.quantity' => ['required', 'array', 'min:1'],
            'items.quantity.*' => ['required', 'integer', 'min:1'],
            'items.unit_price' => ['required', 'array', 'min:1'],
            'items.unit_price.*' => ['required', 'numeric', 'min:0'],
        ]);

        $quote = isset($validated['quote_request_id'])
            ? QuoteRequest::find($validated['quote_request_id'])
            : null;

        $items = $this->lineItems($validated['items']);
        $subtotal = collect($items)->sum('total');
        $tax = round((float) ($validated['tax'] ?? 0), 2);
        $invoiceNumber = $this->nullableTrim($validated['invoice_number'] ?? null) ?: $invoice->invoice_number;

        DB::transaction(function () use ($invoice, $validated, $quote, $items, $subtotal, $tax, $invoiceNumber): void {
            $invoiceData = [
                'invoice_number' => $invoiceNumber,
                'quote_request_id' => $quote?->id,
                'customer_name' => $this->squish($validated['customer_name']),
                'customer_email' => Str::lower(trim($validated['customer_email'])),
                'customer_phone' => trim($validated['customer_phone']),
                'move_origin' => $this->nullableSquish($validated['move_origin'] ?? null),
                'move_destination' => $this->nullableSquish($validated['move_destination'] ?? null),
                'move_date' => $validated['move_date'] ?? null,
                'move_size' => $this->nullableSquish($validated['move_size'] ?? null),
                'quote_reference' => $this->nullableTrim($validated['quote_reference'] ?? null) ?: $quote?->reference(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => round($subtotal + $tax, 2),
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'] ?? null,
            ];

            if (Schema::hasColumn('invoices', 'notes')) {
                $invoiceData['notes'] = $this->nullableTrim($validated['notes'] ?? null);
            }

            $invoice->update($invoiceData);
            $invoice->items()->delete();
            $invoice->items()->createMany($items);
        });

        return redirect()
            ->route('invoice.details', ['invoice' => $invoice->id])
            ->with('toast-success', 'Invoice updated successfully.');
    }

    public function download(Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['items', 'quoteRequest.quotation']);

        return Pdf::loadView('invoices.pdf', $this->invoiceDocumentData($invoice, auth()->user()))
            ->setPaper('a4')
            ->download($this->invoicePdfFilename($invoice));
    }

    public function send(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['items', 'quoteRequest.quotation']);

        if (! in_array($invoice->status, [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_SENT,
            Invoice::STATUS_OVERDUE,
            Invoice::STATUS_UNPAID,
            Invoice::STATUS_PENDING,
            Invoice::STATUS_FAILED,
        ], true)) {
            $message = 'Only draft, sent, overdue, unpaid, pending, or failed invoices can be emailed.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('toast-error', $message);
        }

        $payload = array_merge([
            'recipient_email' => $invoice->customer_email,
            'subject' => $this->defaultInvoiceSubject($invoice),
            'message' => $this->defaultInvoiceMessage($invoice),
            'attach_pdf' => $request->has('attach_pdf') ? $request->boolean('attach_pdf') : true,
        ], $request->only(['recipient_email', 'subject', 'message']));

        $validated = validator($payload, [
            'recipient_email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            'attach_pdf' => ['required', 'boolean'],
        ])->validate();

        $sendResult = $this->sendInvoiceEmailNow($invoice, $validated, $request->user());
        $freshInvoice = $sendResult['invoice'] ?? $invoice->fresh();

        if (! $sendResult['sent'] || $freshInvoice?->status === Invoice::STATUS_FAILED) {
            $message = 'Invoice email failed. Delivery status was logged.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'status' => $freshInvoice->status,
                    'status_label' => $freshInvoice->statusLabel(),
                    'status_badge_class' => $freshInvoice->statusBadgeClass(),
                ], 500);
            }

            return back()->with('toast-error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Invoice email sent successfully.',
                'recipient_email' => Str::lower(trim($validated['recipient_email'])),
                'status' => $freshInvoice?->status,
                'status_label' => $freshInvoice?->statusLabel(),
                'status_badge_class' => $freshInvoice?->statusBadgeClass(),
            ]);
        }

        return back()->with('toast-success', 'Invoice email sent successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()
            ->route('invoice.index')
            ->with('toast-success', 'Invoice deleted successfully.');
    }

    public function markPaid(Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorizeInvoiceAccess($invoice);

        if (! in_array($invoice->status, [
            Invoice::STATUS_SENT,
            Invoice::STATUS_OVERDUE,
            Invoice::STATUS_UNPAID,
            Invoice::STATUS_PENDING,
            Invoice::STATUS_FAILED,
        ], true)) {
            $message = 'Only sent, overdue, unpaid, pending, or failed invoices can be marked as paid.';

            if (request()->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('toast-error', $message);
        }

        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'paid_at' => $invoice->paid_at ?: now(),
        ]);

        $invoice->logStage(
            'PAYMENT_RECEIVED',
            'Invoice marked as paid',
            'admin',
            auth()->user()?->name,
            null,
            'system'
        );

        try {
            \Illuminate\Support\Facades\Mail::to($invoice->customer_email)
                ->send(new \App\Mail\PaymentReceivedMail($invoice));
        } catch (Throwable $exception) {
            report($exception);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Invoice marked as paid.',
                'status' => $invoice->status,
                'status_label' => $invoice->statusLabel(),
                'status_badge_class' => $invoice->statusBadgeClass(),
            ]);
        }

        return back()->with('toast-success', 'Invoice marked as paid.');
    }

    public function markUnpaid(Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorizeInvoiceAccess($invoice);

        if (in_array($invoice->status, [
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_VOID,
            Invoice::STATUS_CANCELLED,
        ], true)) {
            $message = 'Draft, void, or cancelled invoices cannot be marked as unpaid.';

            if (request()->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('toast-error', $message);
        }

        $invoice->update([
            'status' => Invoice::STATUS_UNPAID,
            'paid_at' => null,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Invoice marked as unpaid.',
                'status' => $invoice->status,
                'status_label' => $invoice->statusLabel(),
                'status_badge_class' => $invoice->statusBadgeClass(),
            ]);
        }

        return back()->with('toast-success', 'Invoice marked as unpaid.');
    }

    public function markVoid(Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorizeInvoiceAccess($invoice);

        if (! in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE, Invoice::STATUS_UNPAID], true)) {
            $message = 'Only sent, overdue, or unpaid invoices can be marked as void.';

            if (request()->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('toast-error', $message);
        }

        $invoice->update(['status' => Invoice::STATUS_VOID]);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Invoice marked as void.',
                'status' => $invoice->status,
                'status_label' => $invoice->statusLabel(),
                'status_badge_class' => $invoice->statusBadgeClass(),
            ]);
        }

        return back()->with('toast-success', 'Invoice marked as void.');
    }

    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load('items');

        $copy = DB::transaction(function () use ($invoice): Invoice {
            $copy = $invoice->replicate([
                'invoice_number',
                'status',
                'sent_at',
                'sent_to_email',
                'created_at',
                'updated_at',
            ]);
            $copy->invoice_number = $this->nextInvoiceNumber();
            $copy->status = Invoice::STATUS_DRAFT;
            $copy->sent_at = null;
            $copy->sent_to_email = null;
            $copy->save();

            $copy->items()->createMany($invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])->all());

            return $copy;
        });

        return redirect()
            ->route('invoice.details', ['invoice' => $copy->id])
            ->with('toast-success', 'Invoice duplicated as a draft.');
    }

    private function invoiceDocumentData(Invoice $invoice, ?User $user = null): array
    {
        $invoice->loadMissing(['items', 'quoteRequest.quotation']);
        $company = app(CompanyProfile::class)->data();
        $authorization = app(InvoiceAuthorization::class)->data($invoice, $company, $user);

        return [
            'invoice' => $invoice,
            'company' => $company,
            'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
            'paymentMethods' => app(PaymentSettings::class)->methodsForInvoice($invoice),
            'thankYouMessage' => app(CompanyProfile::class)->thankYouMessage(),
            'authorization' => $authorization,
            'signatureDataUri' => app(InvoiceAuthorization::class)->signatureDataUri($invoice, $company, $user),
            'user' => $user,
        ];
    }

    private function invoicePdfFilename(Invoice $invoice): string
    {
        $invoiceNumber = Str::slug((string) $invoice->invoice_number) ?: (string) $invoice->getKey();
        $customerName = Str::slug((string) $invoice->customer_name) ?: 'customer';

        return "Invoice-{$invoiceNumber}-{$customerName}.pdf";
    }

    private function defaultInvoiceSubject(Invoice $invoice): string
    {
        $company = app(CompanyProfile::class)->data();
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';

        return 'Invoice ' . $invoice->invoice_number . ' from ' . $companyName;
    }

    private function defaultInvoiceMessage(Invoice $invoice): string
    {
        $company = app(CompanyProfile::class)->data();
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';
        $contactLine = collect([$company['email'] ?? null, $company['phone'] ?? null])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(' or ') ?: 'the contact details on the invoice';

        return 'Dear ' . $invoice->customer_name . ",\n\n"
            . 'Please find your invoice ' . $invoice->invoice_number . ' for KES ' . number_format((float) $invoice->total_amount, 2) . ".\n\n"
            . 'Kindly review the attached invoice and use the payment details provided. For any questions, contact us at '
            . $contactLine . ".\n\n"
            . 'Thank you for choosing ' . $companyName . '.';
    }

    private function authorizeInvoiceAccess(Invoice $invoice): void
    {
        abort_unless(auth()->check(), 403);
    }

    private function lineItems(array $items): array
    {
        return collect($items['description'])
            ->map(function (string $description, int $index) use ($items) {
                $quantity = (int) ($items['quantity'][$index] ?? 1);
                $unitPrice = round((float) ($items['unit_price'][$index] ?? 0), 2);

                return [
                    'description' => $this->squish($description),
                    'quantity' => max(1, $quantity),
                    'unit_price' => $unitPrice,
                    'total' => round(max(1, $quantity) * $unitPrice, 2),
                ];
            })
            ->filter(fn (array $item) => $item['description'] !== '')
            ->values()
            ->all();
    }

    private function invoiceLineItemsForQuote(?QuoteRequest $quote): array
    {
        if (! $quote) {
            return [];
        }

        $quote->loadMissing('quotation');
        $quoteAmount = round((float) ($quote->quotation?->quote_amount ?? 0), 2);
        $serviceDescriptions = collect($quote->quotation?->services_included ?? [])
            ->map(fn ($service) => $this->invoiceServiceDescription($service))
            ->filter()
            ->values();

        if ($serviceDescriptions->isEmpty()) {
            return [[
                'description' => $this->invoiceLineDescription($quote),
                'quantity' => 1,
                'unit_price' => $quoteAmount,
            ]];
        }

        return $this->pricedServiceLineItems($serviceDescriptions->all(), $quoteAmount);
    }

    private function invoiceLineDescription(QuoteRequest $quote): string
    {
        $route = trim(collect([$quote->moving_from, $quote->moving_to])->filter()->implode(' to '));
        $parts = collect([
            $quote->serviceTypeLabel(),
            $route !== '' ? $route : null,
            $quote->reference(),
        ])->filter()->values();

        return Str::limit($parts->implode(' - '), 255, '');
    }

    private function invoiceServiceDescription(mixed $service): ?string
    {
        if (is_array($service)) {
            $name = trim((string) ($service['name'] ?? ''));
            $description = trim((string) ($service['description'] ?? ''));
            $label = collect([$name, $description])
                ->filter(fn (string $part) => $part !== '')
                ->implode(' - ');

            return $label !== '' ? Str::limit($label, 255, '') : null;
        }

        if (is_string($service)) {
            $service = trim($service);

            return $service !== '' ? Str::limit($service, 255, '') : null;
        }

        return null;
    }

    /**
     * @param array<int, string> $descriptions
     * @return array<int, array{description: string, quantity: int, unit_price: float}>
     */
    private function pricedServiceLineItems(array $descriptions, float $quoteAmount): array
    {
        $totalCents = max(0, (int) round($quoteAmount * 100));
        $serviceCount = max(1, count($descriptions));
        $baseCents = intdiv($totalCents, $serviceCount);
        $remainderCents = $totalCents % $serviceCount;

        return collect($descriptions)
            ->values()
            ->map(function (string $description, int $index) use ($baseCents, $remainderCents): array {
                $lineCents = $baseCents + ($index < $remainderCents ? 1 : 0);

                return [
                    'description' => $description,
                    'quantity' => 1,
                    'unit_price' => round($lineCents / 100, 2),
                ];
            })
            ->all();
    }

    private function quoteInvoiceLineItems($quotes): array
    {
        return collect($quotes)
            ->mapWithKeys(fn (QuoteRequest $quote) => [
                $quote->id => $this->invoiceLineItemsForQuote($quote),
            ])
            ->all();
    }

    private function invoiceNumber(?string $provided): string
    {
        $provided = $this->nullableTrim($provided);

        if ($provided !== null) {
            return $provided;
        }

        return $this->nextInvoiceNumber();
    }

    private function nextInvoiceNumber(): string
    {
        $nextId = ((int) Invoice::query()->max('id')) + 1;

        do {
            $invoiceNumber = 'INV-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            $nextId++;
        } while (Invoice::query()->where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    private function shouldSendEmailForStatus(string $status): bool
    {
        return in_array($status, [Invoice::STATUS_PENDING, Invoice::STATUS_SENT], true);
    }

    private function sendAndMarkInvoice(Invoice $invoice): bool
    {
        $result = $this->sendInvoiceEmailNow($invoice, [
            'recipient_email' => $invoice->customer_email,
            'subject' => $this->defaultInvoiceSubject($invoice),
            'message' => $this->defaultInvoiceMessage($invoice),
            'attach_pdf' => true,
        ], auth()->user());

        return $result['sent'];
    }

    /**
     * @param array{recipient_email: string, subject: string, message: string, attach_pdf: bool} $payload
     * @return array{sent: bool, invoice: Invoice|null, exception: Throwable|null}
     */
    private function sendInvoiceEmailNow(Invoice $invoice, array $payload, ?User $user = null): array
    {
        $invoice->loadMissing(['items', 'quoteRequest.quotation']);
        $recipient = Str::lower(trim((string) $payload['recipient_email']));
        $subject = $this->squish((string) $payload['subject']);
        $message = trim((string) $payload['message']);
        $emailLog = null;

        try {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invoice email failed: recipient email address is invalid.');
            }

            $this->ensureDeliverableMailTransport('Invoice');
            $invoice->update(['status' => Invoice::STATUS_PENDING]);
            $emailLog = $this->createInvoiceEmailLog($invoice, $recipient, $subject);

            $sentMessage = Mail::to($recipient)->send(new InvoiceMail(
                invoice: $invoice,
                subject: $subject,
                messageBody: $message,
                attachPdf: (bool) $payload['attach_pdf'],
                user: $user,
                emailLogId: $emailLog?->getKey(),
            ));

            $messageId = $this->validatedSentMessageId($sentMessage, 'Invoice');
            $this->markInvoiceSent($invoice, $recipient, $user);
            $this->markEmailLogSent($emailLog);
            $this->recordInvoiceEmailDelivery(
                $invoice,
                Invoice::STATUS_SENT,
                $subject,
                $recipient,
                null,
                'Invoice email accepted by the mail transport. Message ID: '.$messageId,
            );

            return [
                'sent' => true,
                'invoice' => $invoice->fresh(),
                'exception' => null,
            ];
        } catch (Throwable $exception) {
            $invoice->update(['status' => Invoice::STATUS_FAILED]);
            $this->markEmailLogFailed($emailLog, $exception);
            $this->recordInvoiceEmailDelivery($invoice, Invoice::STATUS_FAILED, $subject, $recipient, $exception);
            report($exception);

            return [
                'sent' => false,
                'invoice' => $invoice->fresh(),
                'exception' => $exception,
            ];
        }
    }

    private function markInvoiceSent(Invoice $invoice, string $recipient, ?User $user = null): void
    {
        $data = ['status' => Invoice::STATUS_SENT];

        if (Schema::hasColumn('invoices', 'sent_to_email')) {
            $data['sent_to_email'] = $recipient;
        }

        if (Schema::hasColumn('invoices', 'sent_at')) {
            $data['sent_at'] = now();
        }

        if (Schema::hasColumn('invoices', 'sent_via')) {
            $data['sent_via'] = 'email';
        }

        $invoice->update($data);

        $invoice->logStage(
            'INVOICE_SENT',
            'Invoice sent via email',
            'admin',
            $user?->name,
            null,
            'email'
        );
    }

    private function recordInvoiceEmailDelivery(Invoice $invoice, string $status, string $subject, ?string $recipient = null, ?Throwable $exception = null, ?string $responseMessage = null): void
    {
        if (!Schema::hasTable('email_delivery_logs')) {
            return;
        }

        $now = now();
        $data = [
            'form_type' => 'invoice',
            'recipient_email' => Str::limit((string) ($recipient ?: $invoice->customer_email), 190, ''),
            'status' => $status,
            'direction' => 'client',
            'subject' => Str::limit($subject, 190, ''),
            'transport' => Str::limit((string) config('mail.default'), 190, ''),
            'response_message' => $responseMessage
                ? Str::limit($responseMessage, 1000, '')
                : ($exception
                ? Str::limit($exception->getMessage(), 1000, '')
                : 'Invoice email sent successfully.'),
            'created_at' => $now,
        ];

        if (Schema::hasColumn('email_delivery_logs', 'updated_at')) {
            $data['updated_at'] = $now;
        }

        try {
            DB::table('email_delivery_logs')->insert($data);
        } catch (Throwable $logException) {
            report($logException);
        }
    }

    private function createInvoiceEmailLog(Invoice $invoice, string $recipient, string $subject): ?EmailLog
    {
        if (! Schema::hasTable('email_logs')) {
            return null;
        }

        try {
            return $invoice->emailLogs()->create([
                'recipient_email' => Str::limit($recipient, 190, ''),
                'subject' => Str::limit($subject, 190, ''),
                'status' => EmailLog::STATUS_SENDING,
                'tracking_token' => (string) Str::uuid(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

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
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
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

    private function ensureDeliverableMailTransport(string $documentLabel): void
    {
        if ($this->mailerIsTestDouble()) {
            return;
        }

        $mailer = (string) config('mail.default', '');
        $transport = (string) (config("mail.mailers.{$mailer}.transport") ?: $mailer ?: 'unknown');

        if (in_array(Str::lower($transport), ['array', 'log'], true)) {
            throw new RuntimeException(
                "{$documentLabel} email failed: MAIL_MAILER={$transport} only stores email locally. "
                .'Configure smtp, resend, postmark, mailgun, or ses before sending invoices.'
            );
        }
    }

    private function validatedSentMessageId(mixed $sentMessage, string $documentLabel): string
    {
        if ($this->mailerIsTestDouble() && $sentMessage === null) {
            return 'mail-test-double';
        }

        if (! $sentMessage instanceof SentMessage) {
            throw new RuntimeException("{$documentLabel} email failed: mail transport did not confirm that the message was accepted.");
        }

        $messageId = trim((string) $sentMessage->getMessageId());

        if ($messageId === '') {
            throw new RuntimeException("{$documentLabel} email failed: mail transport accepted the message without a delivery message ID.");
        }

        return $messageId;
    }

    private function mailerIsTestDouble(): bool
    {
        if (! app()->runningUnitTests()) {
            return false;
        }

        $mailer = Mail::getFacadeRoot();

        if (! is_object($mailer)) {
            return false;
        }

        if (is_a($mailer, \Illuminate\Support\Testing\Fakes\MailFake::class)) {
            return true;
        }

        return interface_exists(\Mockery\MockInterface::class)
            && $mailer instanceof \Mockery\MockInterface;
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
