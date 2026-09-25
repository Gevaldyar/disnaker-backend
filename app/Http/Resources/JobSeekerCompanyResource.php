<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobSeekerCompanyResource extends JsonResource
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
            'bio' => $this->bio,
            'city' => $this->city,
            'gender' => $this->gender,

            'phone' => $this->phone,

            'email' => $this->user?->email,

            'portfolio_url' => $this->portfolio_url,
            'linkedin_url' => $this->linkedin_url,

            'cv_available' => !empty($this->cv),

            'cv_url' => $this->cv
                ? route(
                    'company.job-seekers.cv',
                    ['jobSeeker' => $this->id]
                )
                : null,

            'skills' => JobSeekerSkillResource::collection(
                $this->whenLoaded('skills')
            ),

            'educations' => JobSeekerEducationResource::collection(
                $this->whenLoaded('educations')
            ),

            'experiences' => JobSeekerExperienceResource::collection(
                $this->whenLoaded('experiences')
            ),
        ];
    }
}