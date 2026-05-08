<?php

namespace App\Http\Requests;

use App\Models\QuoteRequest as QuoteRequestModel;
use App\Support\LeadCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuoteRequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $serviceType = LeadCategory::normalizeServiceType($this->input('service_type'));

        if ($serviceType !== null) {
            $this->merge([
                'service_type' => $serviceType,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:50'],
            'moving_from' => ['required', 'string', 'max:255'],
            'moving_to' => ['required', 'string', 'max:255'],
            'move_date' => ['nullable', 'date'],
            'service_type' => ['required', 'string', 'max:120', Rule::in(array_keys(QuoteRequestModel::serviceTypeOptions()))],
            'move_size' => ['nullable', 'string', 'max:160'],
            'additional_notes' => ['nullable', 'string'],
            'source_page' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(QuoteRequestModel::statusOptions()))],
        ];
    }

    public function quoteData(?QuoteRequestModel $quote = null): array
    {
        $validated = $this->validated();
        $defaultSourcePage = $quote?->source_page ?: '/admin/quotes';
        $defaultIpAddress = $quote?->ip_address ?: $this->ip();
        $defaultUserAgent = $quote?->user_agent ?: Str::limit((string) $this->userAgent(), 255, '');

        return [
            'full_name' => $this->squish($validated['full_name']),
            'email' => Str::lower(trim($validated['email'])),
            'phone' => trim($validated['phone']),
            'moving_from' => $this->squish($validated['moving_from']),
            'moving_to' => $this->squish($validated['moving_to']),
            'move_date' => $validated['move_date'] ?? null,
            'service_type' => $validated['service_type'],
            'move_size' => $this->nullableSquish($validated['move_size'] ?? null),
            'additional_notes' => $this->nullableTrim($validated['additional_notes'] ?? null),
            'source_page' => $this->nullableTrim($validated['source_page'] ?? null) ?: $defaultSourcePage,
            'ip_address' => $this->nullableTrim($validated['ip_address'] ?? null) ?: $defaultIpAddress,
            'user_agent' => $this->nullableTrim($validated['user_agent'] ?? null) ?: $defaultUserAgent,
            'status' => $validated['status'],
        ];
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }

    private function nullableSquish(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $this->squish($value);
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
