<?php

namespace App\Models;

use App\Support\TopbarData;
use App\Support\LeadCategory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        $flushNotifications = fn () => app(TopbarData::class)->forgetNotifications();

        static::created($flushNotifications);
        static::updated($flushNotifications);
        static::deleted($flushNotifications);
    }

    protected $table = 'messages';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'response',
        'responded_at',
        'responded_by',
        'origin_page',
        'read_at',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'email_log_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'responded_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function respondedByUser()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function latestEmailLog()
    {
        return $this->belongsTo(EmailLog::class, 'email_log_id');
    }

    public function markAsRead()
    {
        if ($this->status === 'unread' || ! $this->read_at) {
            $this->update([
                'status' => $this->status === 'unread' ? 'read' : $this->status,
                'read_at' => $this->read_at ?: now(),
            ]);
        }
    }

    public function respond($response)
    {
        $this->update([
            'response' => $response,
            'status' => 'responded',
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);
    }

    public function categoryLabel(): string
    {
        return LeadCategory::messageCategoryLabel($this->origin_page, $this->subject, $this->message);
    }

    public function categoryBadgeClass(): string
    {
        return LeadCategory::messageCategoryBadgeClass($this->categoryLabel());
    }
}
