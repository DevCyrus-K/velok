<?php

namespace App\Models;

use App\Support\TopbarData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityNotification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
        'url',
        'icon',
        'severity',
        'related_type',
        'related_id',
        'user_id',
        'metadata',
        'occurred_at',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        $flushNotifications = fn () => app(TopbarData::class)->forgetNotifications();

        static::created($flushNotifications);
        static::updated($flushNotifications);
        static::deleted($flushNotifications);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
