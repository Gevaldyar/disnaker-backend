<?php

namespace App\Models;

use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'organizer',
    'location',
    'description',
    'requirements',
    'poster',
    'start_date',
    'end_date',
    'registration_info',
    'registration_link',
    'status',
])]
class Training extends Model
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory;

    protected $appends = ['poster_url'];

    protected function posterUrl(): Attribute
{
    return Attribute::make(
        get: fn ($value, array $attributes) =>
            $attributes['poster']
                ? url(Storage::disk('public')->url($attributes['poster']))
                : null
    );
}

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}