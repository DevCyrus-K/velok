<?php

namespace App\Services;

use App\Models\QuoteRequest;

class QuoteService
{
    public function create(array $data): QuoteRequest
    {
        // Production cleanup: quote creation is available as a service-level operation.
        return QuoteRequest::query()->create($data);
    }

    public function update(QuoteRequest $quote, array $data): QuoteRequest
    {
        $quote->update($data);

        return $quote->refresh();
    }
}
