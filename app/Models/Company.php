<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'description',
    'address',
    'phone',
    'email',
    'website',
    'logo',
    'status',
    'rejection_reason',
    'verified_at',
])]
class Company extends Model
{
    use HasFactory;
    /**
    * Get the jobs posted by the company.
    */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
    /**
     * Company belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }
}