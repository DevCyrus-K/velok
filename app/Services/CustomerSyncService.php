<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Support\LeadCategory;
use Illuminate\Support\Facades\Schema;

class CustomerSyncService
{
    public function sync(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasTable('quote_requests')) {
            return;
        }

        $quotes = QuoteRequest::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        if ($quotes->isEmpty()) {
            Customer::query()
                ->whereNotNull('source_quote_request_id')
                ->delete();
            return;
        }

        $records = $quotes
            ->groupBy(fn (QuoteRequest $quote) => Customer::makeContactKey($quote->email, $quote->phone))
            ->map(function ($group, string $contactKey) {
                /** @var \Illuminate\Support\Collection<int, QuoteRequest> $group */
                $firstQuote = $group->first();
                $latestQuote = $group->last();

                $approvedQuotesCount = $group->whereIn('status', [
                    QuoteRequest::STATUS_QUOTED,
                    QuoteRequest::STATUS_CREATED,
                    QuoteRequest::STATUS_EMAILED,
                ])->count();
                $declinedQuotesCount = $group->filter(fn (QuoteRequest $quote) => in_array($quote->status, [
                    QuoteRequest::STATUS_CLOSED,
                    QuoteRequest::STATUS_SPAM,
                ], true))->count();
                $status = Customer::classifyStatus($latestQuote->status, $latestQuote->created_at);

                return [
                    'contact_key' => $contactKey,
                    'source_quote_request_id' => $latestQuote->id,
                    'full_name' => $latestQuote->full_name,
                    'email' => $latestQuote->email,
                    'phone' => $latestQuote->phone,
                    'moving_from' => $latestQuote->moving_from,
                    'moving_to' => $latestQuote->moving_to,
                    'latest_service_type' => LeadCategory::normalizeServiceType($latestQuote->service_type),
                    'quotes_count' => $group->count(),
                    'approved_quotes_count' => $approvedQuotesCount,
                    'declined_quotes_count' => $declinedQuotesCount,
                    'status' => $status,
                    'first_seen_at' => $firstQuote->created_at,
                    'last_quote_at' => $latestQuote->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        Customer::query()->upsert(
            $records,
            ['contact_key'],
            [
                'source_quote_request_id',
                'full_name',
                'email',
                'phone',
                'moving_from',
                'moving_to',
                'latest_service_type',
                'quotes_count',
                'approved_quotes_count',
                'declined_quotes_count',
                'status',
                'first_seen_at',
                'last_quote_at',
                'updated_at',
            ]
        );

        Customer::query()
            ->whereNotNull('source_quote_request_id')
            ->whereNotIn('contact_key', array_column($records, 'contact_key'))
            ->delete();
    }
}
