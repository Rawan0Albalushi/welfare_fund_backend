<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftMeta extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gift_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donation_id',
        'recipient_name',
        'recipient_phone',
        'message',
        'sender_name',
        'sender_phone',
        'hide_identity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hide_identity' => 'boolean',
    ];

    /**
     * Get the donation that owns the gift meta.
     */
    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }
}
