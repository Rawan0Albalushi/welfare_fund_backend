<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'goal_amount' => $this->goal_amount,
            'raised_amount' => $this->raised_amount,
            'progress_percentage' => $this->progress_percentage,
            'status' => $this->status,
            'status_in_arabic' => $this->status_in_arabic,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'days_remaining' => $this->days_remaining,
            'target_donors' => $this->target_donors,
            'impact_description_ar' => $this->impact_description_ar,
            'impact_description_en' => $this->impact_description_en,
            'campaign_highlights' => $this->campaign_highlights,
            'is_urgent' => $this->is_urgent,
            'is_completed' => $this->is_completed,
            'donors_count' => $this->whenLoaded('donations', function () {
                return $this->donors_count;
            }),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
