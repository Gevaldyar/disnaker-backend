<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'slug',
    'content',
    'image',
    'status',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $appends = ['image_url'];

    /**
     * Get the public URL of the page image.
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