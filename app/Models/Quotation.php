<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
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
