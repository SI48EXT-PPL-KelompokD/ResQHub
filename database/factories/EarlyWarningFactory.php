<?php

namespace Database\Factories;

use App\Models\EarlyWarning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EarlyWarning>
 */
class EarlyWarningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'issued_by' => User::factory()->state(['role' => 'admin']),
            'title' => 'Peringatan Banjir '.fake()->city(),
            'hazard_type' => 'banjir',
            'level' => 'waspada',
            'target_regencies' => ['Kabupaten Bandung'],
            'center_lat' => -6.99,
            'center_lng' => 107.62,
            'radius_km' => 10,
            'instruction' => 'Siapkan tas siaga dan pantau informasi resmi.',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'status' => EarlyWarning::STATUS_DRAFT,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => EarlyWarning::STATUS_ACTIVE]);
    }
}
