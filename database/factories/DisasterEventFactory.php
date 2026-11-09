<?php

namespace Database\Factories;

use App\Models\DisasterEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisasterEvent>
 */
class DisasterEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'name' => 'Banjir ' . fake()->city() . ' ' . fake()->year(),
        'disaster_type' => fake()->randomElement([
            'banjir', 'tanah_longsor', 'gempa_bumi', 'angin_puting_beliung', 'cuaca_ekstrem',
        ]),
        'severity' => fake()->randomElement(['ringan', 'sedang', 'berat']),
        'status' => 'aktif',
        'regency' => fake()->randomElement(['Kab. Bandung', 'Kab. Bandung Barat', 'Kota Bandung']),
        'lat' => fake()->latitude(-7.2, -6.8),
        'lng' => fake()->longitude(107.4, 107.8),
        'started_at' => now()->subDays(fake()->numberBetween(1, 10)),
        'ended_at' => null,
    ];
    }
}
