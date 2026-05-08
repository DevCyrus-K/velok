<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Review extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'reviewer_name',
        'reviewer_role',
        'rating',
        'review_message',
        'photo_path',
        'status',
        'featured',
        'moderation_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'source_page',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'featured' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reference(): string
    {
        return '#REV-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->reviewer_name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'RV';
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DECLINED => 'Declined',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_DECLINED => 'danger',
            default => 'warning',
        };
    }

    public function ratingLabel(): string
    {
        $rating = (float) $this->rating;

        return abs($rating - round($rating)) < 0.001
            ? number_format($rating, 0, '.', '')
            : number_format($rating, 1, '.', '');
    }

    public function photoUrl(): string
    {
        if (Str::startsWith((string) $this->photo_path, ['http://', 'https://', '/'])) {
            return (string) $this->photo_path;
        }

        return Storage::disk('public')->url((string) $this->photo_path);
    }
}
