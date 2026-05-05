<?php

namespace App\Models;

use App\Support\LeadCategory;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    public const STATUS_LEAD = 'lead';
    public const STATUS_ACTIVE_CLIENT = 'active_client';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'customers';

    protected $fillable = [
        'contact_key',
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
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_quote_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function makeContactKey(string $email, string $phone): string
    {
        return Str::lower(trim($email)) . '|' . trim($phone);
    }

    public function reference(): string
    {
        return '#CUS-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->full_name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'CU';
    }

    public function latestRouteSummary(): string
    {
        if ($this->moving_from && $this->moving_to) {
            return trim($this->moving_from . ' to ' . $this->moving_to);
        }

        return $this->moving_from ?: ($this->moving_to ?: 'Route not available');
    }

    public function latestServiceLabel(): string
    {
        return LeadCategory::serviceTypeLabel($this->latest_service_type);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_LEAD => 'Lead',
            self::STATUS_ACTIVE_CLIENT => 'Active Client',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public static function classifyStatus(?string $quoteStatus, ?CarbonInterface $lastActivityAt = null): string
    {
        if ($lastActivityAt && $lastActivityAt->lt(now()->copy()->subMonths(6))) {
            return self::STATUS_INACTIVE;
        }

        return match ($quoteStatus) {
            'closed' => self::STATUS_COMPLETED,
            'processing', 'emailed', 'quoted' => self::STATUS_ACTIVE_CLIENT,
            default => self::STATUS_LEAD,
        };
    }

    public static function normalizeImportedStatus(?string $status, ?CarbonInterface $lastActivityAt = null): string
    {
        $normalized = (string) Str::of((string) $status)
            ->lower()
            ->replace(['-', '_'], ' ')
            ->squish();

        return match ($normalized) {
            'lead', 'new' => self::STATUS_LEAD,
            'active', 'active client', 'approved', 'quoted', 'processing', 'emailed' => self::STATUS_ACTIVE_CLIENT,
            'completed', 'complete', 'closed' => self::STATUS_COMPLETED,
            'inactive', 'declined', 'spam' => self::STATUS_INACTIVE,
            default => self::classifyStatus(null, $lastActivityAt),
        };
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? self::statusOptions()[self::STATUS_LEAD];
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE_CLIENT => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_INACTIVE => 'secondary',
            default => 'warning',
        };
    }

    public function telLink(): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $this->phone);

        return 'tel:' . $phone;
    }

    public function whatsappUrl(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $this->phone);

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '0') && strlen($number) === 10) {
            $number = '254' . substr($number, 1);
        } elseif (str_starts_with($number, '7') && strlen($number) === 9) {
            $number = '254' . $number;
        }

        return 'https://wa.me/' . $number;
    }
}
