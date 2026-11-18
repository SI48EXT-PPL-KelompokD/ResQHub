<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement([
                'pangan',
                'sandang',
                'kebersihan',
                'obat_obatan',
                'perlengkapan_bayi',
                'peralatan',
            ]),
            'unit' => fake()->randomElement(['kg', 'dus', 'pcs', 'box', 'pack', 'lembar']),
            'min_threshold' => fake()->numberBetween(5, 100),
            'is_active' => true,
        ];
    }
}
