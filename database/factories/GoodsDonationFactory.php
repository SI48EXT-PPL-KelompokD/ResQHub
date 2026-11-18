<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\GoodsDonationStatus;
use App\Models\AidNeed;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsDonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'DN-'.now()->format('ym').'-'.fake()->unique()->numerify('####'),
            'donor_id' => User::factory()->state(['role' => 'donatur']),
            'aid_need_id' => AidNeed::query()->value('id'),
            'quantity' => fake()->numberBetween(1, 10),
            'delivery_method' => fake()->randomElement(DeliveryMethod::cases()),
            'est_arrival' => now()->addDays(fake()->numberBetween(1, 7))->toDateString(),
            'status' => GoodsDonationStatus::Dijanjikan,
            'cancel_reason' => null,
        ];
    }

    public function received(): static
    {
        return $this->state(['status' => GoodsDonationStatus::Diterima]);
    }

    public function distributed(): static
    {
        return $this->state(['status' => GoodsDonationStatus::Disalurkan]);
    }

    public function cancelled(string $reason = 'Barang tidak jadi dikirim'): static
    {
        return $this->state([
            'status' => GoodsDonationStatus::Dibatalkan,
            'cancel_reason' => $reason,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(['est_arrival' => now()->subDays(4)->toDateString()]);
    }
}
