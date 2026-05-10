<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookingFlow
{
    public function ensureQuotationTokens(Quotation $quotation, bool $force = false): void
    {
        $needsTokens = $force
            || ! $quotation->approval_token
            || ! $quotation->pdf_token
            || ! $quotation->approval_token_expires_at
            || now()->isAfter($quotation->approval_token_expires_at);

        if (! $needsTokens) {
            return;
        }

        $quotation->update([
            'approval_token' => (string) Str::uuid(),
            'approval_token_expires_at' => now()->addDays(7),
            'pdf_token' => (string) Str::uuid(),
        ]);
    }

    public function updateDepositAmount(Quotation $quotation): void
    {
        $amount = round((float) ($quotation->quote_amount ?? 0) * ((float) ($quotation->deposit_percentage ?? 0) / 100), 2);

        $quotation->forceFill([
            'deposit_amount' => $amount,
        ])->save();
    }

    public function formatPhone(?string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '7') && strlen($phone) === 9) {
            $phone = '254'.$phone;
        }

        return strlen($phone) >= 9 && strlen($phone) <= 15 ? $phone : null;
    }

    public function whatsappUrl(?string $phone, string $message): ?string
    {
        $phone = $this->formatPhone($phone);

        if (! $phone) {
            return null;
        }

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }

    /**
     * @return Collection<int, object{display: string}>
     */
    public function paymentMethodDisplays(?Invoice $invoice = null): Collection
    {
        return collect(app(PaymentSettings::class)->methodsForInvoice($invoice))
            ->flatMap(function (array $method): array {
                $title = trim((string) ($method['title'] ?? 'Payment'));

                return collect($method['rows'] ?? [])
                    ->map(function (array $row) use ($title): object {
                        $label = trim((string) ($row['label'] ?? ''));
                        $value = trim((string) ($row['value'] ?? ''));
                        $display = trim($title.($label !== '' ? ' - '.$label : '').($value !== '' ? ': '.$value : ''));

                        return (object) ['display' => $display];
                    })
                    ->filter(fn (object $row): bool => $row->display !== '')
                    ->values()
                    ->all();
            })
            ->values();
    }

    public function paymentMethodsText(?Invoice $invoice = null): string
    {
        $lines = $this->paymentMethodDisplays($invoice)
            ->pluck('display')
            ->filter()
            ->values();

        return $lines->isNotEmpty() ? $lines->implode("\n") : 'Payment details will be shared by our team.';
    }
}
