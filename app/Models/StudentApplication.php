<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class StudentApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'program_id',
        'personal_json',
        'academic_json',
        'financial_json',
        'status',
        'reject_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_json' => 'array',
        'academic_json' => 'array',
        'financial_json' => 'array',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->application_id)) {
                $model->application_id = 'APP_' . Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * Get the user that owns the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program that owns the application.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Scope a query to only include applications by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include applications under review.
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Scope a query to only include accepted applications.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the application can be edited.
     */
    public function canBeEdited()
    {
        return $this->status === 'under_review';
    }
}
