<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'image',
        'status',
    ];

    /**
     * Get the category that owns the program.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the donations for this program.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the student applications for this program.
     */
    public function studentRegistrations()
    {
        return $this->hasMany(StudentRegistration::class);
    }

    /**
     * Scope a query to only include active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include programs by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to search programs by title.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }
}
