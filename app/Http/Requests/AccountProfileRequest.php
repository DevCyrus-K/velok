<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->getAuthIdentifier();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'signature_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'signature_data' => ['nullable', 'string'],
        ];
    }

    public function profileData(): array
    {
        $validated = $this->validated();

        unset($validated['avatar'], $validated['signature_upload'], $validated['signature_data']);

        return $validated;
    }
}
