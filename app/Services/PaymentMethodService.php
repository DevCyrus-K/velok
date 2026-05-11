<?php

namespace App\Services;

use App\Models\Invoice;
use App\Support\PaymentSettings;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    public function __construct(private readonly PaymentSettings $paymentSettings)
    {
    }

    /**
     * @return Collection<int, object{display_line: string}>
     */
    public function getEnabled(?Invoice $invoice = null): Collection
    {
        return collect($this->paymentSettings->methodsForInvoice($invoice))
            ->flatMap(function (array $method): array {
                $title = trim((string) ($method['title'] ?? 'Payment'));

                return collect($method['rows'] ?? [])
                    ->map(function (array $row) use ($title): object {
                        $label = trim((string) ($row['label'] ?? ''));
                        $value = trim((string) ($row['value'] ?? ''));
                        $displayLine = trim($title.($label !== '' ? ' - '.$label : '').($value !== '' ? ': '.$value : ''));

                        return (object) ['display_line' => $displayLine];
                    })
                    ->filter(fn (object $row): bool => $row->display_line !== '')
                    ->values()
                    ->all();
            })
            ->values();
    }
}
