<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequestFormRequest;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\PaymentMethodService;
use App\Services\StorageService;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\NotificationLogger;
use App\Support\PdfDocumentName;
use App\Support\QuotationEmail;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class QuoteController extends Controller
{
    public function index(): View
    {
        $quotesPaginated = QuoteRequest::query()
            ->with(['quote', 'quotation', 'invoice'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        $allQuotes = QuoteRequest::query()
            ->with(['quote', 'quotation', 'invoice'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('quotes.index', [
            'quotes' => $quotesPaginated,
            'serviceFilters' => $allQuotes->map(fn (QuoteRequest $quote) => $quote->serviceTypeLabel())
                ->unique()
                ->sort()
                ->values(),
            'summary' => [
                'total' => $allQuotes->count(),
                'pending' => $allQuotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'pending')->count(),
                'approved' => $allQuotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'approved')->count(),
                'declined' => $allQuotes->filter(fn (QuoteRequest $quote) => $quote->statusGroup() === 'declined')->count(),
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
        $quote->logStage(
            'REQUEST_SUBMITTED',
            'Quote request created by admin',
            'admin',
            $request->user()?->name,
            $request->ip(),
            'system'
        );

        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-success', 'Quote created successfully.');
    }

    public function show(QuoteRequest $quote): View
    {
        $quote->load(['quote.stages', 'quotation.stages', 'invoice.stages', 'stages']);
        app(NotificationLogger::class)->markReadFor($quote);

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
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh();

        if ($quotation->quote_pdf_storage_key) {
            $quotation->logStage(
                'PDF_DOWNLOADED',
                'Quote PDF downloaded',
                'admin',
                $user?->name,
                null,
                'download'
            );

            return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($quotation->quote_pdf_storage_key));
        }

        $authorization = [
            'name' => $user?->name ?: ($quotation->authorized_by ?: 'Pending'),
            'job_title' => $user?->job_title ?: ($quotation->authorized_role ?: 'Authorized Signatory'),
            'signature_path' => $signaturePath ?: $quotation->signature,
            'is_complete' => $signatureExists,
            'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
            'prompt' => 'Signature not available',
        ];

        $pdf = Pdf::loadView('quotes.pdf', array_merge([
            'quote' => $quote,
            'quotation' => $quotation,
            'company' => app(CompanyProfile::class)->data(),
            'authorization' => $authorization,
            'user' => $user,
            'approvalUrl' => route('quote.customer.approve', ['token' => $quotation->approval_token]),
            'pdfUrl' => route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]),
        ], $this->buildPdfData($user, $quotation, $signaturePath)))->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'enable_html5_parser' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $quotation->logStage(
            'PDF_DOWNLOADED',
            'Quote PDF downloaded',
            'admin',
            $user?->name,
            null,
            'download'
        );

        return $this->redirectToStoredQuotePdf($quotation, $pdf->output(), $this->quotePdfFilename($quote));
    }

    public function publicPdfDownload(int $id, string $token)
    {
        $quotation = Quotation::query()
            ->with('quoteRequest')
            ->whereKey($id)
            ->where('pdf_token', $token)
            ->firstOrFail();

        abort_if(
            $quotation->approval_token_expires_at && now()->isAfter($quotation->approval_token_expires_at),
            403,
            'This PDF download link has expired.'
        );

        if ($quotation->quote_pdf_storage_key) {
            $quotation->logStage(
                'PDF_DOWNLOADED',
                'Customer downloaded quotation PDF',
                'customer',
                $quotation->customer_name,
                request()->ip(),
                'online'
            );

            return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($quotation->quote_pdf_storage_key));
        }

        $pdf = Pdf::loadView('quotes.pdf', array_merge([
            'quote' => $quotation->quoteRequest,
            'quotation' => $quotation,
            'company' => app(CompanyProfile::class)->data(),
            'authorization' => [
                'name' => $quotation->authorized_by ?: 'Authorized Signatory',
                'job_title' => $quotation->authorized_role ?: 'Authorized Signatory',
                'signature_path' => $quotation->signature,
                'is_complete' => filled($quotation->signature),
                'date_label' => $quotation->authorizationDate()?->format('d M Y') ?? now()->format('d M Y'),
                'prompt' => 'Signature not available',
            ],
            'user' => null,
            'approvalUrl' => route('quote.customer.approve', ['token' => $quotation->approval_token]),
            'pdfUrl' => route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]),
        ], $this->buildPdfData(null, $quotation, $quotation->signature)))->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'enable_html5_parser' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $quotation->logStage(
            'PDF_DOWNLOADED',
            'Customer downloaded quotation PDF',
            'customer',
            $quotation->customer_name,
            request()->ip(),
            'online'
        );

        return $this->redirectToStoredQuotePdf($quotation, $pdf->output(), $this->quotePdfFilename($quotation->quoteRequest));
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

        if (! in_array($quote->quotation->status, [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT], true)) {
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
            $result = $quotationEmail->send($quote->quotation, $validated, $request->user());
        } catch (Throwable $exception) {
            // Production hardening: external mail/storage failures are logged with full context.
            Log::error('Quotation email failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Quotation email could not be sent. Please try again.',
                ], 500);
            }

            return back()->with('toast-error', 'Quotation email could not be sent. Please try again.');
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
        $quote->loadMissing('quotation');

        if ($quote->quotation) {
            return redirect()
                ->route('quotes.show', $quote)
                ->with('toast-error', 'Reject or delete the quotation before rejecting this quote request.');
        }

        if ($quote->statusGroup() === 'declined') {
            return redirect()
                ->route('quotes.show', $quote)
                ->with('toast-error', 'This quote request is already rejected.');
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
        return app(PdfDocumentName::class)->quoteRequestFilename($quote);
    }

    private function redirectToStoredQuotePdf(Quotation $quotation, string $contents, string $filename)
    {
        try {
            $uploaded = app(StorageService::class)->uploadGeneratedPdf($contents, $filename, 'quotes');
            $quotation->update([
                'quote_pdf_storage_key' => $uploaded['key'],
                'quote_pdf_storage_file_id' => $uploaded['fileId'],
                'quote_pdf_storage_url' => $uploaded['url'],
                'pdf_storage_key' => $uploaded['key'],
                'pdf_storage_file_id' => $uploaded['fileId'],
                'pdf_storage_url' => $uploaded['url'],
            ]);

            return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($uploaded['key']));
        } catch (\Exception $e) {
            // Fallback: if B2 upload fails, serve PDF directly
            \Log::warning("B2 upload failed for quotation {$quotation->id}: {$e->getMessage()}");
            
            return response()
                ->streamDownload(function () use ($contents) {
                    echo $contents;
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ]);
        }
    }

    private function buildPdfData(?User $user, ?Quotation $quotation = null, ?string $signaturePath = null): array
    {
        $company = app(CompanyProfile::class)->data();
        [$logoBase64, $logoMime] = $this->publicImagePayload($company['logo_path'] ?? 'images/logo-dark.png');

        $signaturePath = $signaturePath ?: $user?->signaturePath() ?: $quotation?->signature;

        $sigBase64 = null;
        $sigMime = 'image/png';
        $signatureDataUri = app(UserSignature::class)->dataUri($signaturePath);

        if (is_string($signatureDataUri) && str_starts_with($signatureDataUri, 'data:')) {
            [$meta, $payload] = array_pad(explode(',', $signatureDataUri, 2), 2, null);
            $sigMime = is_string($meta) && preg_match('/^data:([^;]+)/', $meta, $matches)
                ? $matches[1]
                : 'image/png';
            $sigBase64 = is_string($payload) && $payload !== '' ? $payload : null;
        }

        $companyAddress = collect([$company['address_line_1'] ?? null, $company['address_line_2'] ?? null])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->implode(', ');

        return [
            'user' => $user,
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'sigBase64' => $sigBase64,
            'sigMime' => $sigMime,
            'paymentMethods' => app(PaymentMethodService::class)->getEnabled(),
            'thankYou' => app(CompanyProfile::class)->thankYouMessage(),
            'companyName' => trim((string) ($quotation?->company_name ?? $company['name'] ?? '')) ?: config('app.name'),
            'companyAddress' => $companyAddress,
            'companyPhone' => trim((string) ($quotation?->company_phone ?? $company['phone'] ?? '')),
            'companyEmail' => trim((string) ($quotation?->company_email ?? $company['email'] ?? '')),
            'paymentTerms' => trim((string) ($quotation?->payment_terms ?? '')),
            'cancellation' => trim((string) ($quotation?->cancellationPolicyText() ?? '')),
            'liability' => '',
        ];
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function publicImagePayload(?string $path): array
    {
        $logoDataUri = app(CompanyProfile::class)->logoDataUri();

        if (is_string($logoDataUri) && str_starts_with($logoDataUri, 'data:')) {
            [$meta, $payload] = array_pad(explode(',', $logoDataUri, 2), 2, null);
            $mime = is_string($meta) && preg_match('/^data:([^;]+)/', $meta, $matches)
                ? $matches[1]
                : 'image/png';

            if (is_string($payload) && $payload !== '') {
                return [$payload, $mime];
            }
        }

        $fullPath = public_path(ltrim((string) $path, '/'));

        if (! is_file($fullPath)) {
            return [null, 'image/png'];
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';

        if (! $this->canEmbedImageMime($mime)) {
            $fallbackPath = public_path('images/logo-fallback.svg');

            if (is_file($fallbackPath)) {
                return [
                    base64_encode((string) file_get_contents($fallbackPath)),
                    'image/svg+xml',
                ];
            }

            return [null, 'image/png'];
        }

        return [
            base64_encode((string) file_get_contents($fullPath)),
            $mime,
        ];
    }

    private function canEmbedImageMime(string $mime): bool
    {
        return str_contains($mime, 'svg') || extension_loaded('gd');
    }
}
