<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobSeeker>
 */
class JobSeekerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ak1_number' => 'AK1-' . fake()->unique()->numerify('######'),
            'nik' => fake()->unique()->numerify('327###########'),
            'name' => fake()->name(),
            'birth_place' => 'Tasikmalaya',
            'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years')
                ->format('Y-m-d'),
            'gender' => fake()->randomElement([
                'Laki-laki',
                'Perempuan',
            ]),
            'marital_status' => fake()->randomElement([
                'Belum Kawin',
                'Kawin',
            ]),
            'address' => fake()->address(),
            'phone' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'last_education' => fake()->randomElement([
                'SMA/SMK',
                'D3',
                'S1',
            ]),
            'institution' => fake()->company(),
            'skills' => 'Microsoft Office, Komunikasi',
            'languages' => 'Bahasa Indonesia, Bahasa Inggris',
            'desired_position' => fake()->jobTitle(),
            'desired_location' => 'Tasikmalaya',
            'desired_salary' => 'Rp 2.000.000 - Rp 3.000.000',
            'worked_last_6_months' => fake()->boolean(),
            'status' => 'pending',
            'rejection_reason' => null,
            'verified_at' => null,
        ];
    }
}