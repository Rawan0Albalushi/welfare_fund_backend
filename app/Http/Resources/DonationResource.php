<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'donation_id' => $this->donation_id,
            'amount' => $this->amount,
            'donor_name' => $this->donor_name,
            'type' => $this->type,
            'status' => $this->status,
            'note' => $this->note,
            'expires_at' => $this->expires_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'program' => new ProgramResource($this->whenLoaded('program')),
            'gift_meta' => $this->whenLoaded('giftMeta', function () {
                return [
                    'recipient_name' => $this->giftMeta->recipient_name,
                    'recipient_phone' => $this->giftMeta->recipient_phone,
                    'message' => $this->giftMeta->message,
                    'sender_name' => $this->giftMeta->sender_name,
                    'sender_phone' => $this->giftMeta->sender_phone,
                    'hide_identity' => $this->giftMeta->hide_identity,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
