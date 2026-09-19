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
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();

            $table->string('ak1_number')->nullable()->unique();
            $table->string('nik')->unique();

            $table->string('name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();

            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();

            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->string('last_education')->nullable();
            $table->string('institution')->nullable();

            $table->text('skills')->nullable();
            $table->text('languages')->nullable();

            $table->string('desired_position')->nullable();
            $table->string('desired_location')->nullable();
            $table->string('desired_salary')->nullable();

            $table->boolean('worked_last_6_months')->default(false);

            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seekers');
    }
};