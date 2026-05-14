<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceAgreement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_id',
        'client_id',
        'agreement_reference_no',
        'proposed_move_date',
        'pdf_storage_key',
        'pdf_storage_file_id',
        'pdf_storage_url',
        'email_status',
        'email_sent_at',
        'email_attempts',
    ];

    protected $hidden = [];

    protected $casts = [
        'proposed_move_date' => 'date',
        'email_sent_at' => 'datetime',
        'email_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quote_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }
}
