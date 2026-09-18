<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'title',
    'poster',
    'location',
    'description',
    'published_at',
    'expires_at',
    'views',
    'status',
    'rejection_reason',
    'verified_at',
])]

class Job extends Model
{
    /** @use HasFactory<JobFactory> */
    use HasFactory;

    protected $table = 'job_vacancies';
    protected $appends = ['poster_url'];

    /**
     * Get the public URL of the poster.
    */
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
     * Job belongs to a Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'date',
            'verified_at' => 'datetime',
            'views' => 'integer',
        ];
    }
}