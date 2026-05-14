<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    public function create(array $data): Invoice
    {
        // Production cleanup: invoice creation has a service entry point for controller thinning.
        return Invoice::query()->create($data);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice->refresh();
    }
}
