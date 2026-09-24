<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobSeekerListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'full_name' => $this->full_name,

            'photo_url' => $this->photo
                ? asset('storage/' . $this->photo)
                : null,

            'headline' => $this->headline,
            'city' => $this->city,

            'skills' => JobSeekerSkillResource::collection(
                $this->whenLoaded('skills')
            ),
        ];
    }
}