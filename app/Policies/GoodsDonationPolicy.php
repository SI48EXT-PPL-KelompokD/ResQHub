<?php

namespace App\Policies;

use App\Models\GoodsDonation;
use App\Models\User;

class GoodsDonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDonatur();
    }

    public function create(User $user): bool
    {
        return $user->isDonatur();
    }

    public function view(User $user, GoodsDonation $donation): bool
    {
        return $this->owns($user, $donation);
    }

    public function update(User $user, GoodsDonation $donation): bool
    {
        return $this->owns($user, $donation) && $donation->isPledged();
    }

    public function cancel(User $user, GoodsDonation $donation): bool
    {
        return $this->owns($user, $donation) && $donation->isPledged();
    }

    private function owns(User $user, GoodsDonation $donation): bool
    {
        return $user->isDonatur() && $donation->donor_id === $user->id;
    }
}
