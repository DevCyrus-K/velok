<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoTask extends Model
{
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_COMPLETED = 'completed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ASSIGNED => 'Recently Assigned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function priorityLabel(): string
    {
        return self::priorityOptions()[$this->priority] ?? ucfirst($this->priority);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'badge-outline-success',
            self::STATUS_IN_PROGRESS => 'badge-outline-info',
            self::STATUS_UPCOMING => 'badge-outline-primary',
            default => 'badge-outline-warning',
        };
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'badge-soft-danger',
            self::PRIORITY_HIGH => 'badge-soft-warning',
            self::PRIORITY_LOW => 'badge-soft-success',
            default => 'badge-soft-info',
        };
    }
}
