<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'organizer' => $this->organizer,
            'location' => $this->location,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'poster_url' => $this->poster_url,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'registration_info' => $this->registration_info,
            'registration_link' => $this->registration_link,
        ];
    }
}