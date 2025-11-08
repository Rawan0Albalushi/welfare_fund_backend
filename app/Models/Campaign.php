<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'image',
        'goal_amount',
        'raised_amount',
        'status',
        'start_date',
        'end_date',
        'target_donors',
        'impact_description_ar',
        'impact_description_en',
        'campaign_highlights',
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
        'goal_amount' => 'decimal:2',
        'raised_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'campaign_highlights' => 'array',
    ];

    /**
     * Get the category that owns the campaign.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the donations for this campaign.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Scope a query to only include active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include campaigns by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to search campaigns by title.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%");
    }

    /**
     * Scope a query to only include urgent campaigns (ending soon).
     */
    public function scopeUrgent($query)
    {
        return $query->where('end_date', '<=', now()->addDays(7))
                    ->where('status', 'active');
    }

    /**
     * Scope a query to only include featured campaigns.
     */
    public function scopeFeatured($query)
    {
        return $query->where('status', 'active')
                    ->orderBy('raised_amount', 'desc');
    }

    /**
     * Get the progress percentage of the campaign.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->goal_amount == 0) {
            return 0;
        }
        
        return min(100, round(($this->raised_amount / $this->goal_amount) * 100, 2));
    }

    /**
     * Get the days remaining for the campaign.
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Get the donors count for the campaign.
     */
    public function getDonorsCountAttribute()
    {
        return $this->donations()->where('status', 'paid')->count();
    }

    /**
     * Check if the campaign is urgent (ending within 7 days).
     */
    public function getIsUrgentAttribute()
    {
        return $this->end_date && $this->days_remaining <= 7;
    }

    /**
     * Check if the campaign is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->progress_percentage >= 100 || $this->status === 'completed';
    }

    /**
     * Get the campaign status in Arabic.
     */
    public function getStatusInArabicAttribute()
    {
        $statuses = [
            'draft' => 'مسودة',
            'active' => 'نشط',
            'paused' => 'متوقف مؤقتاً',
            'completed' => 'مكتمل',
            'archived' => 'مؤرشف',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the full URL for the campaign image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return url('/image/campaigns/' . basename($this->image));
    }
}
