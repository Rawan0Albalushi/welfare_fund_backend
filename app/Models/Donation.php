<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donation_id',
        'program_id',
        'campaign_id',
        'amount',
        'donor_name',
        'type',
        'status',
        'payload',
        'user_id',
        'note',
        'expires_at',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->donation_id)) {
                $model->donation_id = 'DN_' . Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * Get the program that owns the donation.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the campaign that owns the donation.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the user that owns the donation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gift meta for this donation.
     */
    public function giftMeta()
    {
        return $this->hasOne(GiftMeta::class);
    }

    /**
     * Scope a query to only include paid donations.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending donations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include gift donations.
     */
    public function scopeGift($query)
    {
        return $query->where('type', 'gift');
    }

    /**
     * Scope a query to only include quick donations.
     */
    public function scopeQuick($query)
    {
        return $query->where('type', 'quick');
    }

    /**
     * Check if the donation is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the donation can be paid.
     */
    public function canBePaid()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }
}
