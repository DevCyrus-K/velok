<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class QuotationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('deposit_percentage') || ! is_numeric($this->input('deposit_percentage'))) {
            return;
        }

        $depositPercentage = (float) $this->input('deposit_percentage');

        $this->merge([
            'deposit_percentage' => min(100, max(0, $depositPercentage)),
        ]);
    }

    public function rules(): array
    {
        return [
            'quote_request_id' => ['required', 'exists:quote_requests,id'],
            'quote_date' => ['required', 'date'],
            'quote_valid_until' => ['required', 'date', 'after:quote_date'],
            'moving_from' => ['required', 'string', 'max:255'],
            'moving_to' => ['required', 'string', 'max:255'],
            'move_date' => ['required', 'date'],
            'quote_amount' => ['required', 'numeric', 'min:0'],
            'deposit_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'cancellation_notice_hours' => ['required', 'integer', 'min:0'],
            'cancellation_policy' => ['nullable', 'string'],
            'services' => ['required', 'array', 'min:1'],
            'services.name' => ['required', 'array', 'min:1'],
            'services.name.*' => ['required', 'string', 'max:255'],
            'services.description' => ['nullable', 'array'],
            'services.description.*' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'action' => ['nullable', 'string', 'in:draft,continue,send,download'],
        ];
    }

    public function servicesIncluded(): array
    {
        $validated = $this->validated();
        $services = [];

        foreach ($validated['services']['name'] ?? [] as $index => $name) {
            $name = trim((string) $name);

            if ($name !== '') {
                $services[] = [
                    'name' => $name,
                    'description' => trim((string) ($validated['services']['description'][$index] ?? '')),
                ];
            }
        }

        return $services;
    }

    public function quotationData(): array
    {
        $validated = $this->validated();

        return [
            'quote_date' => $validated['quote_date'],
            'quote_valid_until' => $validated['quote_valid_until'],
            'moving_from' => $this->squish($validated['moving_from']),
            'moving_to' => $this->squish($validated['moving_to']),
            'move_date' => $validated['move_date'],
            'quote_amount' => round((float) $validated['quote_amount'], 2),
            'deposit_percentage' => min(100, max(0, round((float) $validated['deposit_percentage'], 2))),
            'cancellation_notice_hours' => (int) $validated['cancellation_notice_hours'],
            'cancellation_policy' => trim((string) ($validated['cancellation_policy'] ?? '')) ?: null,
            'additional_notes' => trim((string) ($validated['additional_notes'] ?? '')) ?: null,
            'payment_terms' => trim((string) ($validated['payment_terms'] ?? '')) ?: null,
        ];
    }

    public function submissionAction(): string
    {
        $action = (string) ($this->validated('action') ?? 'continue');

        return in_array($action, ['draft', 'continue', 'send', 'download'], true) ? $action : 'continue';
    }

    private function squish(string $value): string
    {
        return (string) Str::of($value)->squish();
    }
}
