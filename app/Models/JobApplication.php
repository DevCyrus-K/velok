<?php

namespace App\Models;

use App\Support\NotificationLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class JobApplication extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_SHORTLISTED = 'shortlisted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIRED = 'hired';

    protected $fillable = [
        'career_job_id',
        'job_title',
        'applicant_name',
        'email',
        'phone',
        'current_location',
        'resume_url',
        'cover_letter',
        'status',
        'notes',
        'applied_at',
        'source_page',
        'pdf_storage_key',
        'pdf_storage_file_id',
        'pdf_storage_url',
        'legacy_pdf_path',
        'storage_key',
        'storage_url',
        'legacy_file_path',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(fn (JobApplication $application) => app(NotificationLogger::class)->careerApplicationReceived($application));

        static::updated(function (JobApplication $application): void {
            if ($application->wasChanged('status')) {
                app(NotificationLogger::class)->careerApplicationUpdated($application);
            }
        });
    }

    public function careerJob(): BelongsTo
    {
        return $this->belongsTo(CareerJob::class);
    }

    public function reference(): string
    {
        return '#APP-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->applicant_name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'JA';
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_REVIEWING => 'Reviewing',
            self::STATUS_SHORTLISTED => 'Shortlisted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_HIRED => 'Hired',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SHORTLISTED => 'primary',
            self::STATUS_HIRED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_REVIEWING => 'info',
            default => 'warning',
        };
    }

    public function telLink(): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $this->phone);

        return 'tel:'.$phone;
    }

    public function whatsappUrl(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $this->phone);

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '0') && strlen($number) === 10) {
            $number = '254'.substr($number, 1);
        } elseif (str_starts_with($number, '7') && strlen($number) === 9) {
            $number = '254'.$number;
        }

        return 'https://wa.me/'.$number;
    }
}
