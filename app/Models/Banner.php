<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'image',
        'link',
        'status',
        'order',
        'is_featured',
        'start_date',
        'end_date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'image_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'order' => 'integer',
    ];

    /**
     * Scope a query to only include active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope a query to only include featured banners.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to search banners by title.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title_ar', 'like', "%{$search}%")
              ->orWhere('title_en', 'like', "%{$search}%")
              ->orWhere('description_ar', 'like', "%{$search}%")
              ->orWhere('description_en', 'like', "%{$search}%");
        });
    }

    /**
     * Get the full URL for the banner image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return url('/image/banners/' . basename($this->image));
    }

    /**
     * Check if the banner is currently active (within date range).
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }
}
