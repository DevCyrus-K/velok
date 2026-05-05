<?php

namespace App\Models;

use App\Support\TopbarData;
use App\Support\LeadCategory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function respondedByUser()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function markAsRead()
    {
        $this->update(['status' => 'read']);
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
