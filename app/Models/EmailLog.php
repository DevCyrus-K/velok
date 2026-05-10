<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    public const STATUS_SENDING = 'sending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_OPENED = 'opened';

    protected $fillable = [
        'emailable_type',
        'emailable_id',
        'sender_role',
        'sender_email',
        'sender_name',
        'recipient_email',
        'subject',
        'status',
        'tracking_token',
        'sent_at',
        'opened_at',
        'failed_reason',
        'attempts',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'attempts' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmailLog $emailLog): void {
            if (! $emailLog->tracking_token) {
                $emailLog->tracking_token = (string) Str::uuid();
            }
        });
    }

    public function emailable()
    {
        return $this->morphTo();
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeOpened($query)
    {
        return $query->where('status', self::STATUS_OPENED);
    }
}
