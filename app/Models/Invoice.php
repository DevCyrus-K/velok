<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	protected $table = 'invoices';

	public $timestamps = true;

	protected $fillable = [
		'quote_request_id',
		'invoice_date',
		'due_date',
		'status',
		'total_amount',
		'notes',
	];

	protected $casts = [
		'invoice_date' => 'date',
		'due_date' => 'date',
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
}
