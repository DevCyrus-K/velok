<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuoteRequest;
use App\Services\ServiceAgreementService;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
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

            try {
                return redirect()->away(app(StorageService::class)->getPDFDownloadUrl($path));
            } catch (\Exception $e) {
                // Fallback: if B2 download fails, generate and serve directly
                \Log::warning("B2 download failed for service agreement {$path}: {$e->getMessage()}");
                
                $pdf = $agreements->generatePdfContent($quotation, auth()->user());
                $filename = $quotation->service_agreement_filename ?? 'service-agreement-'.now()->format('Y-m-d').'.pdf';
                
                return response()
                    ->streamDownload(function () use ($pdf) {
                        echo $pdf;
                    }, $filename, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                    ]);
            }
        } catch (Throwable $exception) {
            // Production hardening: agreement download failures are logged with full context.
            Log::error('Service agreement download failed', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return $this->downloadErrorResponse($quote, $exception);
        }
    }

    private function downloadErrorResponse(QuoteRequest $quote, Throwable $exception): RedirectResponse
    {
        return redirect()
            ->route('quotes.show', $quote)
            ->with('toast-error', 'Service Agreement PDF could not be prepared. Please try again.');
    }
}
