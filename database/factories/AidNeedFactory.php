<?php

namespace Database\Factories;

use App\Models\AidNeed;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AidNeed>
 */
class AidNeedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shelter_id' => 1,
            'item_id' => Item::factory(),
            'qty_needed' => fake()->numberBetween(10, 300),
            'qty_pledged' => 0,
            'qty_fulfilled' => 0,
            'priority' => fake()->randomElement(['mendesak', 'tinggi', 'normal']),
            'deadline' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'status' => 'terbuka',
            'close_reason' => null,
        ];
    }
}
