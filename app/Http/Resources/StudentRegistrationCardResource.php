<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentRegistrationCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'headline_ar' => $this->headline_ar,
            'headline_en' => $this->headline_en,
            'subtitle_ar' => $this->subtitle_ar,
            'subtitle_en' => $this->subtitle_en,
            'background' => $this->background ?? [],
            'background_image' => $this->background_image,
            'background_image_url' => $this->background_image_url,
            'status' => $this->status,
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

