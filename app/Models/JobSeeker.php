<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'photo',
        'headline',
        'bio',
        'phone',
        'city',
        'address',
        'birth_date',
        'gender',
        'portfolio_url',
        'linkedin_url',
        'cv',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}