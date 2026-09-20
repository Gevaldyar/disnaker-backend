<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Layanan ' . fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'requirements' => 'KTP dan dokumen persyaratan sesuai jenis layanan.',
            'procedure' => 'Isi formulir dan ikuti tahapan pelayanan yang ditentukan.',
            'image' => null,
            'external_link' => null,
            'status' => 'published',
        ];
    }
}