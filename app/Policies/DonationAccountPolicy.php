<?php

namespace App\Policies;

use App\Models\DonationAccount;
use App\Models\User;

class DonationAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, DonationAccount $account): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, DonationAccount $account): bool
    {
        return $user->isAdmin();
    }
}