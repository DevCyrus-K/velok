<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class InvoiceAuthorization
{
    public function data(Invoice $invoice, array $company = [], ?User $user = null): array
    {
        $quotation = $invoice->quoteRequest?->quotation;
        $date = $quotation?->authorizationDate() ?: $invoice->invoice_date ?: now();
        $user ??= auth()->user();
        $signaturePath = $user?->signaturePath();
        $signatureExists = app(UserSignature::class)->exists($signaturePath);
        $companyName = trim((string) ($company['name'] ?? '')) ?: 'Company';

        return [
            'name' => $user?->name ?: ($quotation?->authorized_by ?: $companyName),
            'job_title' => $user?->job_title ?: ($quotation?->authorized_role ?: 'Authorized Signatory'),
            'date_label' => $this->dateLabel($date),
            'signature_path' => $signaturePath ?: $quotation?->signature,
            'signature_url' => $signatureExists ? app(UserSignature::class)->dataUri($signaturePath ?: $quotation?->signature) : null,
            'is_complete' => $signatureExists,
            'profile_url' => auth()->check() ? route('account.show') : null,
        ];
    }

    public function signatureDataUri(Invoice $invoice, array $company = [], ?User $user = null): ?string
    {
        $authorization = $this->data($invoice, $company, $user);

        return app(UserSignature::class)->dataUri($authorization['signature_path']);
    }

    private function dateLabel(mixed $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('d M Y');
        }

        if (is_string($date) && trim($date) !== '') {
            return Carbon::parse($date)->format('d M Y');
        }

        return now()->format('d M Y');
    }
}
