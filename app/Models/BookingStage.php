<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingStage extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function stageable(): MorphTo
    {
        return $this->morphTo();
    }
}
