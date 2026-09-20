<?php

namespace App\Models;

use Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Database table used by this model.
     *
     * Laravel's default "jobs" table is used for queues,
     * while this model uses "job_vacancies" for job listings.
     */
    protected $table = 'job_vacancies';

    /**
     * Attributes appended to the JSON representation.
     */
    protected $appends = [
        'poster_url',
    ];

    /**
     * Get the public URL of the poster.
     */
    protected function posterUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes): ?string {
                $poster = $attributes['poster'] ?? null;

                if (!$poster) {
                    return null;
                }

                return url(
                    Storage::disk('public')->url($poster)
                );
            }
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