<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_type',
        'entity_type',
        'entity_id',
        'payload',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Get the user that owns the audit log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include logs by event type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope a query to only include payment webhook logs.
     */
    public function scopePaymentWebhooks($query)
    {
        return $query->where('event_type', 'payment_webhook');
    }

    /**
     * Scope a query to only include donation events.
     */
    public function scopeDonationEvents($query)
    {
        return $query->where('event_type', 'like', 'donation_%');
    }
}
