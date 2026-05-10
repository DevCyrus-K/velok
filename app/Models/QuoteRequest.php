<?php

namespace App\Models;

use App\Support\LeadCategory;
use App\Support\NotificationLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_EMAILED = 'emailed';
    public const STATUS_EMAIL_FAILED = 'email_failed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CREATED = 'created';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_SPAM = 'spam';

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
        'approval_date',
    ];

    protected $casts = [
        'move_date' => 'date',
        'approval_date' => 'date',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(fn (QuoteRequest $quote) => app(NotificationLogger::class)->quoteRequestReceived($quote));

        static::updated(function (QuoteRequest $quote): void {
            if ($quote->wasChanged('status')) {
                app(NotificationLogger::class)->quoteRequestUpdated($quote);
            }
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_EMAILED => 'Emailed',
            self::STATUS_EMAIL_FAILED => 'Email Failed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_QUOTED => 'Approved',
            self::STATUS_CREATED => 'Created',
            self::STATUS_CLOSED => 'Rejected',
            self::STATUS_SPAM => 'Spam',
        ];
    }

    public static function serviceTypeOptions(): array
    {
        return collect(LeadCategory::serviceTypes())
            ->mapWithKeys(fn (string $serviceType) => [$serviceType => $serviceType])
            ->all();
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

    public function getCustomerNameAttribute(): ?string
    {
        return $this->full_name;
    }

    public function getPickupLocationAttribute(): ?string
    {
        return $this->moving_from;
    }

    public function getDropoffLocationAttribute(): ?string
    {
        return $this->moving_to;
    }

    public function getPreferredMoveDateAttribute()
    {
        return $this->move_date;
    }

    public function getItemDetailsAttribute(): ?string
    {
        return $this->move_size;
    }

    public function getSpecialNotesAttribute(): ?string
    {
        return $this->additional_notes;
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
            self::STATUS_QUOTED, self::STATUS_CREATED => 'success',
            self::STATUS_PROCESSING, self::STATUS_EMAILED => 'info',
            self::STATUS_EMAIL_FAILED, self::STATUS_SPAM => 'danger',
            self::STATUS_CLOSED => 'secondary',
            default => 'warning',
        };
    }

    public function statusGroup(): string
    {
        return match ($this->status) {
            self::STATUS_QUOTED, self::STATUS_CREATED, self::STATUS_EMAILED => 'approved',
            self::STATUS_CLOSED, self::STATUS_SPAM => 'declined',
            default => 'pending',
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

    public function quote()
    {
        return $this->hasOne(Quotation::class, 'quote_request_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'quote_request_id');
    }
}
