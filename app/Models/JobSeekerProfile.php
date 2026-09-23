<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\JobSeekerSkill;
use App\Models\JobSeekerEducation;
use App\Models\JobSeekerExperience;

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
    * Get the educations owned by the job seeker.
    */
    public function educations(): HasMany
    {
        return $this->hasMany(JobSeekerEducation::class);
    }

    /**
    * Get the skills owned by the job seeker.
    */
    public function skills(): HasMany
    {
    return $this->hasMany(JobSeekerSkill::class);
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
    * Get the work experiences owned by the job seeker.
    */
    public function experiences(): HasMany
    {
        return $this->hasMany(JobSeekerExperience::class);
    }
}