<?php

namespace Database\Factories;

use App\Models\DisasterReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisasterReport>
 */
class DisasterReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory()->state(['role' => 'warga']),
            'disaster_type' => 'banjir',
            'title' => 'Banjir di '.fake()->streetName(),
            'description' => fake()->sentence(12),
            'estimated_affected' => fake()->numberBetween(5, 200),
            'lat' => -6.99,
            'lng' => 107.62,
            'address' => fake()->address(),
            'occurred_at' => now()->subHours(3),
            'status' => DisasterReport::STATUS_PENDING,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => DisasterReport::STATUS_ACCEPTED,
            'verified_at' => now(),
        ]);
    }
}
