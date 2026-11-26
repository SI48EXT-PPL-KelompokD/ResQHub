<?php

namespace Database\Factories;

use App\Models\AidPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AidPackage> */
class AidPackageFactory extends Factory
{
    protected $model = AidPackage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'target' => fake()->randomElement(['keluarga', 'jiwa', 'balita', 'lansia']),
            'description' => fake()->sentence(),
            'is_active' => true,
            'created_by' => User::factory()->create(['role' => 'koordinator']),
        ];
    }
}
