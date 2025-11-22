<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentRegistrationCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'headline_ar',
        'headline_en',
        'subtitle_ar',
        'subtitle_en',
        'background',
        'background_image',
        'status',
    ];

    protected $casts = [
        'background' => 'array',
    ];

    protected $appends = [
        'background_image_url',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        if (empty($this->background_image)) {
            return null;
        }

        return url('/image/student-registration-cards/' . basename($this->background_image));
    }

    public static function defaultPayload(): array
    {
        return [
            'headline_ar' => 'قدّم طلبك وابدأ رحلتك بثقة',
            'headline_en' => 'Apply today and start your journey with confidence',
            'subtitle_ar' => 'نحن هنا لتمكينك من مواصلة تعليمك',
            'subtitle_en' => 'We are here to empower you to continue your education',
            'background' => [
                'type' => 'gradient',
                'color_from' => '#9f5bff',
                'color_to' => '#f782c1',
            ],
            'status' => 'active',
        ];
    }
}

