<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingStage extends Model
{
    public $timestamps = false;

    // Production hardening: replace open guarding with explicit mass-assignable stage fields.
    protected $fillable = [
        'stageable_type',
        'stageable_id',
        'stage',
        'description',
        'triggered_by',
        'actor_name',
        'actor_ip',
        'channel',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $hidden = [];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function stageable(): MorphTo
    {
        return $this->morphTo();
    }
}
