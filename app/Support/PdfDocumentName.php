<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\QuoteRequest;
use Illuminate\Support\Str;

class PdfDocumentName
{
    public function invoiceTitle(Invoice $invoice): string
    {
        $invoiceNumber = trim((string) $invoice->invoice_number) ?: (string) $invoice->getKey();

        return "Invoice {$invoiceNumber}";
    }

    public function invoiceFilename(Invoice $invoice): string
    {
        return $this->filename([
            $this->invoiceTitle($invoice),
            $invoice->customer_name,
        ]);
    }

    public function quotationTitle(Quotation $quotation): string
    {
        $reference = trim((string) $quotation->reference) ?: (string) $quotation->getKey();

        return "Quotation {$reference}";
    }

    public function quotationFilename(Quotation $quotation): string
    {
        return $this->filename([
            $this->quotationTitle($quotation),
            $quotation->customer_name,
        ]);
    }

    public function quoteRequestTitle(QuoteRequest $quote): string
    {
        $reference = trim((string) $quote->reference()) ?: (string) $quote->getKey();

        return "Quotation {$reference}";
    }

    public function quoteRequestFilename(QuoteRequest $quote): string
    {
        return $this->filename([
            $this->quoteRequestTitle($quote),
            $quote->full_name,
        ]);
    }

    private function filename(array $parts): string
    {
        $slug = collect($parts)
            ->map(fn ($part): string => Str::slug((string) $part))
            ->filter()
            ->implode('-');

        return ($slug !== '' ? $slug : 'document').'.pdf';
    }
}
