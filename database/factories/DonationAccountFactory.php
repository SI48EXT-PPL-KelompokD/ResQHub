<?php

namespace Database\Factories;

use App\Models\DisasterEvent;
use App\Models\DonationAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationAccount>
 */
class DonationAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'disaster_event_id' => DisasterEvent::factory(),
            'bank_name' => fake()->randomElement([
                'Bank Rakyat Indonesia (BRI)',
                'Bank Mandiri',
                'Bank Central Asia (BCA)',
                'Bank Negara Indonesia (BNI)',
            ]),
            'account_number' => fake()->unique()->numerify('####-##-######'),
            'account_holder' => 'BPBD ' . fake()->city() . ' Peduli Bencana',
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Rekening berstatus nonaktif.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}