<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function depositAmount(): float
    {
        return (float) ($this->quote_amount ?? 0) * ((float) ($this->deposit_percentage ?? 0) / 100);
    }

    public function balanceDue(): float
    {
        return max(0, (float) ($this->quote_amount ?? 0) - $this->depositAmount());
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
