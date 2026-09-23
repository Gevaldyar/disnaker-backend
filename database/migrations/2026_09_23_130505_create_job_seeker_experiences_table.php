<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seeker_experiences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_seeker_profile_id')
                ->constrained('job_seeker_profiles')
                ->cascadeOnDelete();

            $table->string('company_name');
            $table->string('position');
            $table->string('employment_type')->nullable();

            $table->string('location')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_current')->default(false);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_experiences');
    }
};