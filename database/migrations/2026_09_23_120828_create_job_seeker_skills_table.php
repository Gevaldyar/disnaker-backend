<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seeker_skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_seeker_profile_id')
                ->constrained('job_seeker_profiles')
                ->cascadeOnDelete();

            $table->string('skill_name');
            $table->string('level')->nullable();

            $table->timestamps();

            $table->unique([
                'job_seeker_profile_id',
                'skill_name',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_skills');
    }
};