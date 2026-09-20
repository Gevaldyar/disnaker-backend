<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'slug',
    'description',
    'requirements',
    'procedure',
    'image',
    'external_link',
    'status',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $appends = ['image_url'];

    /**
     * Get the public URL of the service image.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) =>
                $attributes['image']
                    ? url(Storage::disk('public')->url($attributes['image']))
                    : null
        );
    }
}