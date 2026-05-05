<?php

namespace App\Support;

use Illuminate\Support\Str;

class LeadCategory
{
    public const MOVING_RELOCATION = 'Moving & Relocation';
    public const INVOICE = 'Invoice';
    public const FEEDBACK = 'Feedback';
    public const GENERAL = 'General';

    public static function normalizeServiceType(?string $value): ?string
    {
        $normalized = self::clean($value);

        if ($normalized === null) {
            return null;
        }

        $key = Str::lower($normalized);

        return match ($key) {
            'moving',
            'move',
            'relocation',
            'moving and relocation',
            'moving & relocation' => self::MOVING_RELOCATION,
            'office',
            'office move',
            'office moving' => 'Office Move',
            'commercial',
            'commercial move',
            'commercial moving' => 'Commercial Move',
            'residential',
            'residential move',
            'home moving' => 'Residential Move',
            'storage',
            'storage and packing',
            'packing',
            'packing and storage' => 'Storage & Packing',
            'warehouse',
            'warehouse move' => 'Warehouse Move',
            default => Str::of($normalized)->headline()->replace(' And ', ' & ')->toString(),
        };
    }

    public static function serviceTypeLabel(?string $value, string $fallback = 'Unknown Service'): string
    {
        return self::normalizeServiceType($value) ?? $fallback;
    }

    public static function messageCategoryLabel(?string $originPage, ?string $subject = null, ?string $message = null): string
    {
        $haystack = Str::lower(implode(' ', array_filter([
            self::clean($originPage),
            self::clean($subject),
            self::clean($message),
        ])));

        if ($haystack === '') {
            return self::GENERAL;
        }

        if (Str::contains($haystack, ['moving', 'move', 'relocation', 'quote'])) {
            return self::MOVING_RELOCATION;
        }

        if (Str::contains($haystack, ['invoice', 'payment', 'billing'])) {
            return self::INVOICE;
        }

        if (Str::contains($haystack, ['feedback', 'review', 'complaint'])) {
            return self::FEEDBACK;
        }

        return self::GENERAL;
    }

    public static function messageCategoryBadgeClass(?string $category): string
    {
        return match ($category) {
            self::MOVING_RELOCATION => 'primary',
            self::INVOICE => 'info',
            self::FEEDBACK => 'warning',
            default => 'secondary',
        };
    }

    private static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $cleaned = (string) Str::of($value)->squish();

        return $cleaned === '' ? null : $cleaned;
    }
}
