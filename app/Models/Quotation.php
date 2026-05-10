<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quotation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'quotations';

    public $timestamps = true;

    protected $fillable = [
        'quote_request_id',
        'company_name',
        'company_email',
        'company_phone',
        'company_website',
        'quote_date',
        'quote_valid_until',
        'deposit_percentage',
        'cancellation_notice_hours',
        'cancellation_policy',
        'services_included',
        'additional_notes',
        'payment_terms',
        'status',
        'sent_at',
        'sent_via',
        'approval_token',
        'approval_token_expires_at',
        'pdf_token',
        'approved_by_name',
        'approval_ip',
        'approval_method',
        'deposit_amount',
        'deposit_paid',
        'deposit_paid_at',
        'deposit_reference',
        'deposit_method',
        'deposit_whatsapp_url',
        'reminder_whatsapp_url',
        'followup_whatsapp_url',
        'moving_from',
        'moving_to',
        'move_date',
        'quote_amount',
        'authorized_by',
        'authorized_role',
        'approval_date',
        'signature',
        'signature_type',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'quote_valid_until' => 'date',
        'sent_at' => 'datetime',
        'approval_token_expires_at' => 'datetime',
        'deposit_paid' => 'boolean',
        'deposit_paid_at' => 'datetime',
        'move_date' => 'date',
        'approval_date' => 'date',
        'services_included' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quoteRequest()
    {
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'quote_request_id', 'quote_request_id');
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
        string $actorName = null,
        string $actorIp = null,
        string $channel = null,
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

    public function depositAmount(): float
    {
        if ($this->deposit_amount !== null) {
            return (float) $this->deposit_amount;
        }

        return (float) ($this->quote_amount ?? 0) * ((float) ($this->deposit_percentage ?? 0) / 100);
    }

    public function balanceDue(): float
    {
        return max(0, (float) ($this->quote_amount ?? 0) - $this->depositAmount());
    }

    public function getReferenceAttribute(): string
    {
        return $this->quoteRequest?->reference() ?: '#QT'.str_pad((string) $this->getKey(), 5, '0', STR_PAD_LEFT);
    }

    public function getCustomerNameAttribute(): ?string
    {
        return $this->quoteRequest?->full_name;
    }

    public function getCustomerEmailAttribute(): ?string
    {
        return $this->quoteRequest?->email;
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->quoteRequest?->phone;
    }

    public function getPickupLocationAttribute(): ?string
    {
        return $this->moving_from ?: $this->quoteRequest?->moving_from;
    }

    public function getDropoffLocationAttribute(): ?string
    {
        return $this->moving_to ?: $this->quoteRequest?->moving_to;
    }

    public function getValidUntilAttribute()
    {
        return $this->quote_valid_until;
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->quote_amount ?? 0);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quote_amount ?? 0);
    }

    public function getDiscountAttribute(): float
    {
        return 0.0;
    }

    public function getBalanceAttribute(): float
    {
        return $this->balanceDue();
    }

    public function getContactPreferenceAttribute(): string
    {
        $preference = $this->quoteRequest?->contact_preference;

        return in_array($preference, ['email', 'whatsapp', 'both'], true) ? $preference : 'both';
    }

    public function validityDays(): ?int
    {
        if (! $this->quote_date || ! $this->quote_valid_until) {
            return null;
        }

        return max(0, (int) round($this->quote_date->copy()->startOfDay()->diffInDays($this->quote_valid_until->copy()->startOfDay(), false)));
    }

    public function cancellationPolicyText(): string
    {
        return $this->cancellation_policy
            ?: 'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.';
    }

    public function authorizationDate()
    {
        return $this->approval_date ?: $this->quoteRequest?->approval_date ?: now();
    }

    public function getServicesIncludedAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    public function setServicesIncludedAttribute($value)
    {
        $this->attributes['services_included'] = is_array($value) ? json_encode($value) : $value;
    }
}
