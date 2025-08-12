<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentRegistrationResource extends JsonResource
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
            'registration_id' => $this->registration_id,
            'personal' => $this->personal_json,
            'academic' => $this->academic_json,
            'financial' => $this->financial_json,
            'status' => $this->status,
            'reject_reason' => $this->reject_reason,
            'id_card_image' => $this->id_card_image,
            'program' => new ProgramResource($this->whenLoaded('program')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
