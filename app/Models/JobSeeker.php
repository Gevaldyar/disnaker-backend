<?php

namespace App\Models;

use Database\Factories\JobSeekerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'ak1_number',
    'nik',
    'name',
    'birth_place',
    'birth_date',
    'gender',
    'marital_status',
    'address',
    'phone',
    'email',
    'last_education',
    'institution',
    'skills',
    'languages',
    'desired_position',
    'desired_location',
    'desired_salary',
    'worked_last_6_months',
    'status',
    'rejection_reason',
    'verified_at',
])]
class JobSeeker extends Model
{
    /** @use HasFactory<JobSeekerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'worked_last_6_months' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}