<?php

namespace App\Models;

use App\Support\LeadCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    protected $table = 'quote_requests';

    public $timestamps = true;
    public const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'moving_from',
        'moving_to',
        'move_date',
        'service_type',
        'move_size',
        'additional_notes',
        'source_page',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'move_date' => 'date',
        'created_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            'new' => 'New',
            'emailed' => 'Emailed',
            'email_failed' => 'Email Failed',
            'processing' => 'Processing',
            'quoted' => 'Approved',
            'closed' => 'Declined',
            'spam' => 'Spam',
        ];
    }

    public function reference(): string
    {
        return '#QT' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->full_name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'QT';
    }

    public function routeSummary(): string
    {
        return trim($this->moving_from . ' to ' . $this->moving_to);
    }

    public function serviceTypeLabel(): string
    {
        return LeadCategory::serviceTypeLabel($this->service_type);
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'quoted' => 'success',
            'processing', 'emailed' => 'info',
            'email_failed', 'spam' => 'danger',
            'closed' => 'secondary',
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

    public function quotation()
    {
        return $this->hasOne(Quotation::class, 'quote_request_id');
    }
}
