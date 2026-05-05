<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $table = 'gallery';

    protected $casts = [
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
