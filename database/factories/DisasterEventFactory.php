<?php

namespace Database\Factories;

use App\Models\DisasterEvent;
use App\Models\DisasterReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisasterEvent>
 */
class DisasterEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Banjir '.fake()->city(),
            'disaster_type' => fake()->randomElement(DisasterReport::DISASTER_TYPES),
            'severity' => fake()->randomElement(['ringan', 'sedang', 'berat']),
            'status' => 'aktif',
            'regency' => 'Kabupaten Bandung',
            'lat' => -6.99,
            'lng' => 107.62,
            'started_at' => now()->subDay(),
        ];
    }
}
