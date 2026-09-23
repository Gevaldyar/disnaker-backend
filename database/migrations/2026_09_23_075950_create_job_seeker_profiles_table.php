<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_seeker_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('full_name');
            $table->string('photo')->nullable();

            $table->string('headline')->nullable();
            $table->text('bio')->nullable();

            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();

            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->string('cv')->nullable();

            $table->boolean('is_public')->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeker_profiles');
    }
};