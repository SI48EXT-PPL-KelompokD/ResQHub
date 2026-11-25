<?php

namespace Database\Factories;

use App\Models\DisasterEvent;
use App\Models\Shelter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shelter>
 */
class ShelterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'disaster_event_id' => DisasterEvent::factory(),
            'coordinator_id' => User::factory(),
            'name' => 'Posko '.fake()->streetName(),
            'address' => fake()->streetAddress().', Kabupaten Bandung',
            'lat' => fake()->latitude(-7.1, -6.8),
            'lng' => fake()->longitude(107.5, 107.8),
            'capacity' => 200,
            'occupancy' => 0,
            'contact' => fake()->numerify('0812########'),
            'status' => 'aktif',
            'close_reason' => null,
        ];
    }

    public function closed(string $reason = 'Kejadian sudah tertangani'): static
    {
        return $this->state(fn () => ['status' => 'ditutup', 'close_reason' => $reason]);
    }
}
