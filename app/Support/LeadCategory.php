<?php

namespace App\Support;

use Illuminate\Support\Str;

class LeadCategory
{
    public const RESIDENTIAL_RELOCATION = 'Residential Relocation';
    public const OFFICE_RELOCATION = 'Office Relocation';
    public const LONG_DISTANCE_MOVE = 'Long-Distance Move';
    public const PACKING_STORAGE = 'Packing & Storage';
    public const MOVING_RELOCATION = self::RESIDENTIAL_RELOCATION;
    public const INVOICE = 'Invoice';
    public const FEEDBACK = 'Feedback';
    public const GENERAL = 'General';

    public static function serviceTypes(): array
    {
        return [
            self::RESIDENTIAL_RELOCATION,
            self::OFFICE_RELOCATION,
            self::LONG_DISTANCE_MOVE,
            self::PACKING_STORAGE,
        ];
    }

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
            'moving & relocation',
            'residential',
            'residential move',
            'home moving',
            'residential relocation' => self::RESIDENTIAL_RELOCATION,
            'office',
            'office move',
            'office moving',
            'office relocation',
            'commercial',
            'commercial move',
            'commercial moving',
            'warehouse',
            'warehouse move' => self::OFFICE_RELOCATION,
            'long-distance move',
            'long distance move',
            'long-distance relocation',
            'long distance relocation' => self::LONG_DISTANCE_MOVE,
            'storage',
            'storage and packing',
            'storage & packing',
            'packing',
            'packing and storage',
            'packing & storage' => self::PACKING_STORAGE,
            default => in_array($normalized, self::serviceTypes(), true) ? $normalized : null,
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
