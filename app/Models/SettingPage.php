<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'title_ar',
        'title_en',
        'content_ar',
        'content_en',
    ];

    /**
     * Get the setting page by key.
     *
     * @param string $key
     * @return SettingPage|null
     */
    public static function findByKey(string $key): ?SettingPage
    {
        return static::where('key', $key)->first();
    }
}
