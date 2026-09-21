<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'thumbnail_url' => $this->thumbnail_url,
            'instagram_url' => $this->instagram_url,
            'published_at' => $this->published_at,
        ];
    }
}