<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationFormRequest;
use App\Mail\DepositReceivedMail;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\ServiceAgreementService;
use App\Services\StorageService;
use App\Support\BookingFlow;
use App\Support\CompanyProfile;
use App\Support\PdfDocumentName;
use App\Support\QuotationEmail;
use App\Support\UserSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class QuotationController extends Controller
{
    private const DEFAULT_PAYMENT_TERMS = '50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.';

    private const DEFAULT_CANCELLATION_POLICY = 'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.';

    private const DEFAULT_CANCELLATION_NOTICE_HOURS = 48;

    public function __construct(
        private readonly UserSignature $userSignature,
    ) {}

    public function create(QuoteRequest $quote, Request $request)
    {
        $quotation = Quotation::where('quote_request_id', $quote->id)->first() ?? new Quotation;

        if (! $quotation->exists && in_array($quote->status, [
            QuoteRequest::STATUS_NEW,
            QuoteRequest::STATUS_QUOTED,
            QuoteRequest::STATUS_EMAIL_FAILED,
        ], true)) {
            $quote->update(['status' => QuoteRequest::STATUS_PROCESSING]);
        }

        return view('quotations.create', $this->formViewData($quote, $quotation, $request->user()));
    }

    public function store(QuotationFormRequest $request)
    {
        $validated = $request->quotationData();
        $action = $request->submissionAction();
        $quote = QuoteRequest::findOrFail($request->validated('quote_request_id'));
        $services = $request->servicesIncluded();
        $authorization = $this->authorizationStorageData($request->user(), $quote);
        $company = app(CompanyProfile::class)->data();

        $quotation = DB::transaction(function () use ($quote, $validated, $services, $authorization, $company) {
            $quotation = Quotation::updateOrCreate(
                ['quote_request_id' => $quote->id],
                [
                    'company_name' => $company['name'] ?? '',
                    'company_email' => $company['email'] ?? '',
                    'company_phone' => $company['phone'] ?? '',
                    'company_website' => $company['website'] ?? null,
                    'quote_date' => $validated['quote_date'],
                    'quote_valid_until' => $validated['quote_valid_until'],
                    'moving_from' => $validated['moving_from'],
                    'moving_to' => $validated['moving_to'],
                    'move_date' => $validated['move_date'],
                    'quote_amount' => $validated['quote_amount'],
                    'deposit_percentage' => $validated['deposit_percentage'],
                    'deposit_amount' => $this->depositAmount($validated),
                    'cancellation_notice_hours' => $validated['cancellation_notice_hours'],
                    'cancellation_policy' => $validated['cancellation_policy'] ?: self::DEFAULT_CANCELLATION_POLICY,
                    'services_included' => $services,
                    'additional_notes' => $validated['additional_notes'] ?? null,
                    'payment_terms' => $validated['payment_terms'] ?: self::DEFAULT_PAYMENT_TERMS,
                    'authorized_by' => $authorization['authorized_by'],
                    'authorized_role' => $authorization['authorized_role'],
                    'approval_date' => $authorization['approval_date'],
                    'signature' => $authorization['signature'],
                    'signature_type' => $authorization['signature'] ? 'image' : null,
                    'status' => 'draft',
                    'sent_at' => null,
                ]
            );

            $quote->update(['status' => QuoteRequest::STATUS_CREATED]);
            $quotation->logStage(
                'QUOTE_CREATED',
                'Quotation created by admin',
                'admin',
                auth()->user()?->name,
                null,
                'system'
            );

            return $quotation;
        });

        return $this->quotationActionResponse($quotation, $action, 'Quotation created successfully.');
    }

    public function update(QuotationFormRequest $request, Quotation $quotation)
    {
        $validated = $request->quotationData();
        $action = $request->submissionAction();
        $services = $request->servicesIncluded();
        $quote = QuoteRequest::findOrFail($request->validated('quote_request_id'));
        $authorization = $this->authorizationStorageData($request->user(), $quote, $quotation);
        $company = app(CompanyProfile::class)->data();

        DB::transaction(function () use ($quotation, $quote, $validated, $services, $authorization, $action, $company): void {
            $quotationData = [
                'company_name' => $quotation->company_name ?: ($company['name'] ?? ''),
                'company_email' => $quotation->company_email ?: ($company['email'] ?? ''),
                'company_phone' => $quotation->company_phone ?: ($company['phone'] ?? ''),
                'company_website' => $quotation->company_website ?: ($company['website'] ?? null),
                'quote_date' => $validated['quote_date'],
                'quote_valid_until' => $validated['quote_valid_until'],
                'moving_from' => $validated['moving_from'],
                'moving_to' => $validated['moving_to'],
                'move_date' => $validated['move_date'],
                'quote_amount' => $validated['quote_amount'],
                'deposit_percentage' => $validated['deposit_percentage'],
                'deposit_amount' => $this->depositAmount($validated),
                'cancellation_notice_hours' => $validated['cancellation_notice_hours'],
                'cancellation_policy' => $validated['cancellation_policy'] ?: self::DEFAULT_CANCELLATION_POLICY,
                'services_included' => $services,
                'additional_notes' => $validated['additional_notes'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?: self::DEFAULT_PAYMENT_TERMS,
                'authorized_by' => $authorization['authorized_by'],
                'authorized_role' => $authorization['authorized_role'],
                'approval_date' => $authorization['approval_date'],
                'signature' => $authorization['signature'],
                'signature_type' => $authorization['signature'] ? 'image' : null,
            ];

            if ($action === 'draft') {
                $quotationData['status'] = 'draft';
                $quotationData['sent_at'] = null;
            }

            $quotation->update($quotationData);

            $quote->update([
                'status' => $this->quoteRequestStatusAfterQuotationSave($quotation, $action),
            ]);
            $quotation->logStage(
                'QUOTE_UPDATED',
                'Quotation updated by admin',
                'admin',
                auth()->user()?->name,
                null,
                'system'
            );
        });

        return $this->quotationActionResponse($quotation, $action, 'Quotation updated successfully!');
    }

    public function show(Quotation $quotation, Request $request)
    {
        $quotation->loadMissing(['quoteRequest', 'invoice', 'emailLogs', 'stages']);
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh()->loadMissing(['quoteRequest', 'invoice', 'emailLogs', 'stages']);

        return view('quotations.show', [
            'quotation' => $quotation,
            'authorization' => $this->authorizationViewData($request->user(), $quotation, $quotation->quoteRequest),
            'approvalUrl' => route('quote.customer.approve', ['token' => $quotation->approval_token]),
            'pdfUrl' => route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]),
            'whatsappUrl' => $this->buildWhatsAppMessage($quotation),
        ]);
    }

    public function edit(Quotation $quotation, Request $request)
    {
        abort_if($quotation->status !== Quotation::STATUS_DRAFT, 403);

        $quote = $quotation->quoteRequest;

        return view('quotations.create', $this->formViewData($quote, $quotation, $request->user()));
    }

    public function pdf(Quotation $quotation, Request $request)
    {
        $quotation->loadMissing('quoteRequest');
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh()->loadMissing('quoteRequest');
        $authorization = $this->authorizationViewData($request->user(), $quotation, $quotation->quoteRequest);
        $user = $request->user();

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

        $pdf = Pdf::loadView('quotes.pdf', [
            'quote' => $quotation->quoteRequest,
            'quotation' => $quotation,
            'company' => app(CompanyProfile::class)->data(),
            'logoDataUri' => app(CompanyProfile::class)->logoDataUri(),
            'authorization' => $authorization,
            'signatureDataUri' => $this->userSignature->dataUri($authorization['signature_path']),
            'user' => $user,
            'paymentMethods' => app(BookingFlow::class)->paymentMethodDisplays(),
            'thankYouMessage' => app(CompanyProfile::class)->thankYouMessage(),
            'approvalUrl' => route('quote.customer.approve', ['token' => $quotation->approval_token]),
            'pdfUrl' => route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]),
        ])->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi' => 150,
                'enable_html5_parser' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Inter',
            ]);

        $quotation->logStage(
            'PDF_DOWNLOADED',
            'Quote PDF downloaded',
            'admin',
            $user?->name,
            null,
            'download'
        );

        try {
            $uploaded = app(StorageService::class)->uploadGeneratedPdf(
                $pdf->output(),
                app(PdfDocumentName::class)->quotationFilename($quotation),
                'quotes'
            );

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
                ->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, app(PdfDocumentName::class)->quotationFilename($quotation), [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.app(PdfDocumentName::class)->quotationFilename($quotation).'"'
                ]);
        }
    }

    public function send(Request $request, Quotation $quotation, QuotationEmail $quotationEmail): RedirectResponse|JsonResponse
    {
        $quotation->loadMissing('quoteRequest');

        if (! in_array($quotation->status, [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT], true)) {
            $message = 'Only draft or sent quotations can be emailed.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('quotations.show', $quotation)->with('toast-error', $message);
        }

        $payload = array_merge([
            'recipient_email' => $quotation->quoteRequest->email,
            'subject' => $quotationEmail->defaultSubject($quotation),
            'message' => $quotationEmail->defaultMessage($quotation, $request->user()),
            'attach_pdf' => $request->has('attach_pdf') ? $request->boolean('attach_pdf') : true,
        ], $request->only(['recipient_email', 'subject', 'message']));

        $validated = validator($payload, [
            'recipient_email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            'attach_pdf' => ['required', 'boolean'],
        ])->validate();

        try {
            app(BookingFlow::class)->ensureQuotationTokens($quotation);
            $result = $quotationEmail->send($quotation, $validated, $request->user());
        } catch (Throwable $exception) {
            // Production hardening: quotation mail failures are logged with full context.
            Log::error('Quotation email failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Quotation email failed. Delivery status was logged.',
                ], 500);
            }

            return redirect()->route('quotations.show', $quotation)
                ->with('toast-error', 'Quotation email failed. Delivery status was logged.');
        }

        if ($result['status'] !== 'sent') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Quotation email failed. Delivery status was logged.',
                ], 500);
            }

            return redirect()->route('quotations.show', $quotation)
                ->with('toast-error', 'Quotation email failed. Delivery status was logged.');
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

        return redirect()->route('quotations.show', $quotation)
            ->with('toast-success', $message);
    }

    public function markSent(Request $request, Quotation $quotation): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:email,whatsapp,link'],
        ]);

        $quotation->loadMissing('quoteRequest');
        app(BookingFlow::class)->ensureQuotationTokens($quotation);

        DB::transaction(function () use ($quotation, $validated): void {
            $quotation->update([
                'status' => Quotation::STATUS_SENT,
                'sent_at' => now(),
                'sent_via' => $validated['channel'],
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_EMAILED,
            ]);
        });

        $quotation->logStage(
            'QUOTE_SENT',
            "Quotation sent via {$validated['channel']}",
            'admin',
            auth()->user()?->name,
            null,
            $validated['channel']
        );

        return response()->json(['success' => true]);
    }

    public function approve(Quotation $quotation, ServiceAgreementService $serviceAgreements): RedirectResponse
    {
        $quotation->loadMissing('quoteRequest');

        if ($quotation->status !== Quotation::STATUS_SENT) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('toast-error', 'Only sent quotations can be approved.');
        }

        DB::transaction(function () use ($quotation): void {
            $quotation->update([
                'status' => 'approved',
                'approval_date' => $quotation->approval_date ?: now()->toDateString(),
            ]);

            $quotation->quoteRequest?->update([
                'status' => QuoteRequest::STATUS_CREATED,
                'approval_date' => $quotation->quoteRequest?->approval_date ?: now()->toDateString(),
            ]);
            $quotation->logStage(
                'APPROVED_ADMIN',
                'Quotation approved by admin',
                'admin',
                auth()->user()?->name,
                null,
                'system'
            );
        });

        $quotation->refresh()->loadMissing('quoteRequest');

        try {
            $agreementResult = $serviceAgreements->generateAndSendForApprovedQuotation($quotation, auth()->user());
            $message = $agreementResult['emailed']
                ? 'Quotation approved successfully. Service Agreement generated and emailed to the client.'
                : 'Quotation approved successfully. Service Agreement generated, but email delivery failed and the admin alert was logged.';

            return redirect()
                ->route('quotations.show', $quotation)
                ->with($agreementResult['emailed'] ? 'toast-success' : 'toast-error', $message);
        } catch (Throwable $exception) {
            Log::error('Service agreement generation failed after quotation approval', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('quotations.show', $quotation)
                ->with('toast-error', 'Quotation approved, but the Service Agreement could not be generated. Please try again.');
        }
    }

    public function reject(Quotation $quotation): RedirectResponse
    {
        $quotation->loadMissing('quoteRequest');

        if ($quotation->status !== Quotation::STATUS_SENT) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('toast-error', 'Only sent quotations can be rejected.');
        }

        DB::transaction(function () use ($quotation): void {
            $quotation->update(['status' => Quotation::STATUS_DECLINED]);
            $quotation->quoteRequest?->update(['status' => QuoteRequest::STATUS_CLOSED]);
            $quotation->logStage(
                'REJECTED',
                'Quotation rejected by admin',
                'admin',
                auth()->user()?->name,
                null,
                'system'
            );
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('toast-success', 'Quotation rejected successfully.');
    }

    public function duplicate(Quotation $quotation): RedirectResponse
    {
        $quotation->loadMissing('quoteRequest');

        $copy = $quotation->replicate([
            'status',
            'sent_at',
            'quote_date',
            'quote_valid_until',
            'approval_date',
            'created_at',
            'updated_at',
        ]);
        $copy->status = 'draft';
        $copy->sent_at = null;
        $copy->quote_date = now()->toDateString();
        $copy->quote_valid_until = now()->addDays(7)->toDateString();
        $copy->approval_date = now()->toDateString();
        $copy->save();

        return redirect()
            ->route('quotations.show', $copy)
            ->with('toast-success', 'Quotation duplicated as a draft.');
    }

    public function markDepositReceived(Request $request, Quotation $quotation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['required', 'string', 'max:190'],
            'method' => ['required', 'string', 'max:80'],
        ]);

        $quotation->loadMissing('quoteRequest');

        $quotation->update([
            'deposit_amount' => round((float) $validated['amount'], 2),
            'deposit_paid' => true,
            'deposit_paid_at' => now(),
            'deposit_reference' => (string) Str::of($validated['reference'])->squish(),
            'deposit_method' => (string) Str::of($validated['method'])->squish(),
        ]);

        $quotation->logStage(
            'DEPOSIT_RECEIVED',
            'Deposit received - KES '
                .number_format((float) $validated['amount'], 2)
                .' - Ref: '.$validated['reference']
                .' via '.$validated['method'],
            'admin',
            auth()->user()?->name
        );

        $quotation->logStage(
            'BOOKING_CONFIRMED',
            'Booking confirmed after deposit receipt',
            'system'
        );

        try {
            $this->notifyCustomerDepositReceived($quotation);
        } catch (Throwable $exception) {
            Log::error('Deposit received notification failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('toast-success', 'Deposit marked as received and booking confirmed.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->loadMissing(['quoteRequest', 'invoice']);
        $quote = $quotation->quoteRequest;

        if ($quotation->invoice) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('toast-error', 'Delete the invoice before deleting this quotation.');
        }

        DB::transaction(function () use ($quotation, $quote): void {
            $this->deleteStoredQuotationFiles($quotation);
            $quotation->delete();

            if ($quote) {
                $quote->update(['status' => QuoteRequest::STATUS_QUOTED]);
            }
        });

        return $quote
            ? redirect()->route('quotes.show', $quote)->with('toast-success', 'Quotation deleted successfully.')
            : redirect()->route('quotes.index')->with('toast-success', 'Quotation deleted successfully.');
    }

    private function deleteStoredQuotationFiles(Quotation $quotation): void
    {
        $storage = app(StorageService::class);

        if ($quotation->quote_pdf_storage_file_id && $quotation->quote_pdf_storage_key) {
            $storage->deletePDF($quotation->quote_pdf_storage_file_id, $quotation->quote_pdf_storage_key);
        }

        if ($quotation->service_agreement_storage_file_id && $quotation->service_agreement_path) {
            $storage->deletePDF($quotation->service_agreement_storage_file_id, $quotation->service_agreement_path);
        }

        if ($quotation->image_public_id) {
            $storage->deleteImage($quotation->image_public_id);
        }
    }

    private function formViewData(QuoteRequest $quote, Quotation $quotation, ?User $user): array
    {
        return [
            'quote' => $quote,
            'quotation' => $quotation,
            'autofill' => $this->autofillData($quote, $quotation),
            'authorization' => $this->authorizationViewData($user, $quotation, $quote),
            'company' => app(CompanyProfile::class)->data(),
            'serviceTypeOptions' => QuoteRequest::serviceTypeOptions(),
        ];
    }

    private function depositAmount(array $validated): float
    {
        return round((float) ($validated['quote_amount'] ?? 0) * ((float) ($validated['deposit_percentage'] ?? 0) / 100), 2);
    }

    private function autofillData(QuoteRequest $quote, Quotation $quotation): array
    {
        $quoteDate = $quotation->quote_date?->format('Y-m-d') ?: now()->format('Y-m-d');
        $validUntil = $quotation->quote_valid_until?->format('Y-m-d') ?: now()->addDays(7)->format('Y-m-d');
        $validityDays = max(0, (int) round(now()->copy()->startOfDay()->diffInDays(now()->copy()->addDays(7)->startOfDay(), false)));

        if ($quotation->quote_date && $quotation->quote_valid_until) {
            $validityDays = $quotation->validityDays() ?? $validityDays;
        }

        return [
            'customer_name' => $quote->customer_name,
            'contact_info' => trim($quote->email.' • '.$quote->phone),
            'service_type' => $quote->serviceTypeLabel(),
            'pickup_location' => $quotation->moving_from ?: $quote->pickup_location,
            'dropoff_location' => $quotation->moving_to ?: $quote->dropoff_location,
            'preferred_move_date' => $quotation->move_date?->format('Y-m-d') ?: $quote->preferred_move_date?->format('Y-m-d'),
            'preferred_move_date_label' => $quotation->move_date?->format('d M Y') ?: $quote->preferred_move_date?->format('d M Y') ?: 'Not specified',
            'item_details' => $quote->item_details ?: 'Not specified',
            'special_notes' => $quote->special_notes ?: '',
            'quote_date' => $quoteDate,
            'quote_valid_until' => $validUntil,
            'quote_validity_days' => $validityDays,
            'payment_terms' => $quotation->payment_terms ?: self::DEFAULT_PAYMENT_TERMS,
            'cancellation_notice_hours' => $quotation->cancellation_notice_hours ?: self::DEFAULT_CANCELLATION_NOTICE_HOURS,
            'cancellation_policy' => $quotation->cancellation_policy ?: self::DEFAULT_CANCELLATION_POLICY,
        ];
    }

    private function authorizationStorageData(?User $user, QuoteRequest $quote, ?Quotation $quotation = null): array
    {
        $approvalDate = $quote->approval_date?->format('Y-m-d')
            ?: $quotation?->approval_date?->format('Y-m-d')
            ?: now()->toDateString();

        return [
            'authorized_by' => $user?->name,
            'authorized_role' => $user?->job_title,
            'approval_date' => $approvalDate,
            'signature' => $this->userSignature->path($user),
        ];
    }

    private function authorizationViewData(?User $user, ?Quotation $quotation = null, ?QuoteRequest $quote = null): array
    {
        $userSignaturePath = $this->userSignature->path($user);
        $signaturePath = $user ? $userSignaturePath : $quotation?->signature;
        $date = $quotation?->approval_date ?: $quote?->approval_date ?: now();
        $dateValue = $date instanceof CarbonInterface ? $date->format('Y-m-d') : now()->toDateString();
        $dateLabel = $date instanceof CarbonInterface ? $date->format('d M Y') : now()->format('d M Y');
        $name = $user ? $user->name : $quotation?->authorized_by;
        $jobTitle = $user ? $user->job_title : $quotation?->authorized_role;
        $hasSignature = $this->userSignature->exists($signaturePath);

        return [
            'name' => $name ?: 'Pending',
            'job_title' => $jobTitle ?: 'Authorized Signatory',
            'signature_path' => $signaturePath,
            'signature_url' => $hasSignature ? $this->userSignature->dataUri($signaturePath) : null,
            'is_complete' => $hasSignature,
            'profile_url' => route('account.show'),
            'date_value' => $dateValue,
            'date_label' => $dateLabel,
            'prompt' => 'No signature on file.',
        ];
    }

    private function quotationActionResponse(Quotation $quotation, string $action, string $successMessage)
    {
        $quotation->refresh()->loadMissing('quoteRequest');

        if ($action === 'send') {
            $quotationEmail = app(QuotationEmail::class);

            try {
                $result = $quotationEmail->send($quotation, [
                    'recipient_email' => $quotation->quoteRequest->email,
                    'subject' => $quotationEmail->defaultSubject($quotation),
                    'message' => $quotationEmail->defaultMessage($quotation, auth()->user()),
                    'attach_pdf' => true,
                ], auth()->user());
            } catch (Throwable $exception) {
                Log::error('Quotation send action failed', [
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                return redirect()->route('quotations.show', $quotation)
                    ->with('toast-error', 'Quotation saved, but the email could not be sent.');
            }

            if ($result['status'] === 'sent') {
                return redirect()->route('quotations.show', $quotation)
                    ->with('toast-success', 'Quotation saved and sent to client successfully.');
            }

            return redirect()->route('quotations.show', $quotation)
                ->with('toast-error', 'Quotation saved, but the email failed. Delivery status was logged.');
        }

        if ($action === 'download') {
            return redirect()->route('quotations.pdf', $quotation);
        }

        $message = $action === 'draft' ? 'Quotation saved as draft.' : $successMessage;

        return redirect()->route('quotations.show', $quotation)
            ->with('toast-success', $message);
    }

    private function buildWhatsAppMessage(Quotation $quotation): ?string
    {
        $quotation->loadMissing('quoteRequest');
        app(BookingFlow::class)->ensureQuotationTokens($quotation);
        $quotation->refresh()->loadMissing('quoteRequest');

        $approvalUrl = route('quote.customer.approve', ['token' => $quotation->approval_token]);
        $pdfUrl = route('quote.pdf.download', ['id' => $quotation->id, 'token' => $quotation->pdf_token]);
        $moveDate = $quotation->move_date?->format('d M Y') ?? $quotation->quoteRequest?->move_date?->format('d M Y') ?? 'To be confirmed';
        $validUntil = $quotation->quote_valid_until?->format('d M Y') ?? now()->addDays(7)->format('d M Y');
        $companyName = trim((string) (app(CompanyProfile::class)->data()['name'] ?: 'Kwikshift Movers'));

        $message = "Hello {$quotation->customer_name}! 👋\n\n"
            ."Thank you for choosing *{$companyName}* 🚛\n\n"
            ."Please find your quotation details:\n\n"
            ."📋 *Quote Number:* {$quotation->reference}\n"
            ."📅 *Move Date:* {$moveDate}\n"
            ."📍 *From:* {$quotation->pickup_location}\n"
            ."📍 *To:* {$quotation->dropoff_location}\n"
            .'💰 *Total:* KES '.number_format($quotation->total, 2)."\n"
            .'💳 *Deposit Required:* KES '.number_format($quotation->depositAmount(), 2)."\n"
            ."⏳ *Valid Until:* {$validUntil}\n\n"
            ."📄 *Download Quote PDF:*\n{$pdfUrl}\n\n"
            ."✅ *Approve Your Quotation:*\n{$approvalUrl}\n\n"
            ."💳 *Pay Deposit to Confirm Booking:*\n"
            .app(BookingFlow::class)->paymentMethodsText()."\n\n"
            ."_For any questions reply to this message_\n\n"
            ."*{$companyName} Team* 🚛";

        return app(BookingFlow::class)->whatsappUrl($quotation->customer_phone, $message);
    }

    private function notifyCustomerDepositReceived(Quotation $quotation): void
    {
        $quotation->loadMissing('quoteRequest');
        $preference = $quotation->contact_preference;

        if (in_array($preference, ['email', 'both'], true)) {
            Mail::to($quotation->customer_email)
                ->send(new DepositReceivedMail($quotation));
        }

        if (in_array($preference, ['whatsapp', 'both'], true)) {
            $message = "Hello {$quotation->customer_name}! ✅\n\n"
                ."*Deposit Received!*\n"
                .'Amount: KES '.number_format($quotation->depositAmount(), 2)."\n"
                ."Reference: {$quotation->deposit_reference}\n\n"
                ."*Your booking is now CONFIRMED* 🎉\n"
                .'📅 Move Date: '.($quotation->move_date?->format('d M Y') ?? 'To be confirmed')."\n"
                ."📍 Pickup: {$quotation->pickup_location}\n"
                ."📍 Drop-off: {$quotation->dropoff_location}\n\n"
                .'Balance Due on Move Day: KES '.number_format($quotation->balanceDue(), 2)."\n\n"
                .'We will see you on '.($quotation->move_date?->format('d M Y') ?? 'move day')."! 🚛\n"
                .'*'.config('app.name').' Team*';

            $quotation->update([
                'deposit_whatsapp_url' => app(BookingFlow::class)->whatsappUrl($quotation->customer_phone, $message),
            ]);
        }
    }

    private function quoteRequestStatusAfterQuotationSave(Quotation $quotation, string $action): string
    {
        if ($action !== 'draft' && $quotation->status === 'sent') {
            return QuoteRequest::STATUS_EMAILED;
        }

        return QuoteRequest::STATUS_CREATED;
    }
}
