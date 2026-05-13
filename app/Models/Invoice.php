<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public const STATUS_PAID = 'paid';

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PENDING = 'pending';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SENT = 'sent';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_VOID = 'void';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'invoices';

    public $timestamps = true;

    protected $fillable = [
        'invoice_number',
        'quote_request_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'move_origin',
        'move_destination',
        'move_date',
        'move_size',
        'quote_reference',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax',
        'status',
        'sent_at',
        'sent_via',
        'sent_to_email',
        'paid_at',
        'total_amount',
        'payment_method',
        'notes',
        'pdf_storage_key',
        'pdf_storage_file_id',
        'pdf_storage_url',
        'legacy_pdf_path',
        'storage_key',
        'storage_url',
        'legacy_file_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'move_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PAID => 'Paid',
            self::STATUS_UNPAID => 'Unpaid',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_SENT => 'Sent',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_VOID => 'Void',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function paymentMethodOptions(): array
    {
        return [
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'card' => 'Card',
            'cheque' => 'Cheque',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) ($this->status ?: 'draft'));
    }

    public function statusBadgeClass(): string
    {
        return match ((string) $this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_SENT => 'info',
            self::STATUS_OVERDUE => 'danger',
            self::STATUS_VOID, self::STATUS_CANCELLED => 'secondary',
            self::STATUS_PENDING, self::STATUS_UNPAID, self::STATUS_DRAFT => 'warning',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }

    public function emailLogs()
    {
        return $this->morphMany(EmailLog::class, 'emailable')->latest();
    }

    public function stages(): MorphMany
    {
        return $this->morphMany(BookingStage::class, 'stageable')
            ->orderBy('created_at', 'asc');
    }

    public function logStage(
        string $stage,
        string $description,
        string $triggeredBy = 'system',
        ?string $actorName = null,
        ?string $actorIp = null,
        ?string $channel = null,
        array $metadata = []
    ): void {
        $this->stages()->create([
            'stage' => $stage,
            'description' => $description,
            'triggered_by' => $triggeredBy,
            'actor_name' => $actorName,
            'actor_ip' => $actorIp,
            'channel' => $channel,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    public function paymentMethodLabel(): string
    {
        $method = trim((string) $this->payment_method);

        return self::paymentMethodOptions()[$method] ?? ($method !== '' ? Str::headline(str_replace('_', ' ', $method)) : 'To be agreed');
    }

    public function customerInitials(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->customer_name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::substr($word, 0, 1))
            ->implode('');

        return Str::upper($initials !== '' ? $initials : 'IN');
    }

    public function getReferenceAttribute(): string
    {
        return (string) $this->invoice_number;
    }

    public function getPickupLocationAttribute(): ?string
    {
        return $this->move_origin;
    }

    public function getDropoffLocationAttribute(): ?string
    {
        return $this->move_destination;
    }

    public function getCustomerAddressAttribute(): string
    {
        return collect([$this->move_origin, $this->move_destination])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->implode(' to ');
    }

    public function getDiscountAttribute(): float
    {
        return 0.0;
    }

    public function getDepositAmountAttribute(): float
    {
        return (float) ($this->quoteRequest?->quotation?->deposit_amount ?? 0);
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->total_amount ?? 0);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->total - $this->deposit_amount);
    }
}
