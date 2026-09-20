<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyJobResource extends JsonResource
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
            'title' => $this->title,
            'poster_url' => $this->poster_url,
            'location' => $this->location,
            'description' => $this->description,

            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,

            'views' => $this->views,
            'status' => $this->status,

            'rejection_reason' => $this->rejection_reason,
            'verified_at' => $this->verified_at,
        ];
    }
}