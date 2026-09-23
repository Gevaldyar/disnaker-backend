<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobSeekerProfileResource extends JsonResource
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
            'user_id' => $this->user_id,

            'full_name' => $this->full_name,
            'photo_url' => $this->photo
                ? asset('storage/' . $this->photo)
                : null,

            'headline' => $this->headline,
            'bio' => $this->bio,

            'phone' => $this->phone,
            'city' => $this->city,
            'address' => $this->address,

            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,

            'portfolio_url' => $this->portfolio_url,
            'linkedin_url' => $this->linkedin_url,

            'cv_url' => $this->cv
                ? asset('storage/' . $this->cv)
                : null,

            'is_public' => $this->is_public,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}