<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerSkill extends Model
{
    protected $fillable = [
        'job_seeker_profile_id',
        'skill_name',
        'level',
    ];

    /**
     * Get the job seeker profile that owns this skill.
     */
    public function jobSeekerProfile(): BelongsTo
    {
        return $this->belongsTo(JobSeekerProfile::class);
    }
}