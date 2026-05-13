<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Services\ServiceAgreementService;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ServiceAgreementController extends Controller
{
    public function download(QuoteRequest $quote, ServiceAgreementService $agreements)
    {
        $quote->loadMissing('quotation');
        $quotation = $quote->quotation;

        abort_unless($quotation instanceof Quotation, 404);
        abort_unless($quotation->status === Quotation::STATUS_APPROVED, 404);

        try {
            $path = $quotation->service_agreement_path;

            if (! is_string($path) || $path === '' || ! app(StorageService::class)->exists($path)) {
                $path = $agreements->generateForApprovedQuotation($quotation, auth()->user());
                $quotation->refresh();
            }

            return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($path));
        } catch (Throwable $exception) {
            report($exception);

            return $this->downloadErrorResponse($quote, $exception);
        }
    }

    private function downloadErrorResponse(QuoteRequest $quote, Throwable $exception): RedirectResponse
    {
        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-error', $exception->getMessage());
    }
}
