<?php

namespace Database\Factories;

use App\Models\Shelter;
use App\Models\ShelterFacility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShelterFacility>
 */
class ShelterFacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shelter_id' => Shelter::factory(),
            'type' => fake()->randomElement(array_keys(ShelterFacility::TYPES)),
            'units' => fake()->numberBetween(1, 10),
            'capacity' => fake()->numberBetween(10, 200),
            'condition' => 'baik',
            'note' => null,
            'photo_path' => null,
        ];
    }

    public function condition(string $condition): static
    {
        return $this->state(fn () => ['condition' => $condition]);
    }

    public function broken(): static
    {
        return $this->condition('tidak_berfungsi');
    }
}
