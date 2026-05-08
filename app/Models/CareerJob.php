<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CareerJob extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'title',
        'slug',
        'department',
        'location',
        'employment_type',
        'salary_range',
        'summary',
        'description',
        'requirements',
        'status',
        'posted_at',
        'closes_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'closes_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (CareerJob $job) {
            if (!$job->slug || $job->isDirty('title')) {
                $job->slug = self::uniqueSlug($job->title, $job->id);
            }

            if ($job->status === self::STATUS_OPEN && !$job->posted_at) {
                $job->posted_at = now();
            }
        });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function reference(): string
    {
        return '#JOB-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_OPEN => 'Open',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public static function employmentTypeOptions(): array
    {
        return [
            'Full-time',
            'Part-time',
            'Contract',
            'Internship',
            'Temporary',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'success',
            self::STATUS_CLOSED => 'secondary',
            default => 'warning',
        };
    }

    private static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'career-job';
        $slug = $base;
        $counter = 2;

        while (self::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
