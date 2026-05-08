<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryItem extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'gallery';

    protected $fillable = [
        'title',
        'image_path',
        'image_url',
        'thumbnail_url',
        'description',
        'category',
        'alt_text',
        'featured',
        'status',
        'order',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function reference(): string
    {
        return '#GAL-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_ARCHIVED => 'secondary',
            default => 'warning',
        };
    }

    public function imagePath(): string
    {
        return (string) ($this->image_path ?: $this->image_url ?: '');
    }

    public function publicUrl(): string
    {
        $path = $this->imagePath();

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return '/' . ltrim($path, '/');
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return route('gallery.asset', ['path' => ltrim($path, '/')]);
    }

    public function altText(): string
    {
        return (string) ($this->alt_text ?: ($this->description ?: $this->title ?: ''));
    }
}
