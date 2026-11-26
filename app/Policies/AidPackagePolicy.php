<?php

namespace App\Policies;

use App\Models\AidPackage;
use App\Models\User;

class AidPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isKoordinator();
    }

    public function view(User $user, AidPackage $package): bool
    {
        return $user->isKoordinator();
    }

    public function create(User $user): bool
    {
        return $user->isKoordinator();
    }

    public function update(User $user, AidPackage $package): bool
    {
        return $user->isKoordinator();
    }

    public function delete(User $user, AidPackage $package): bool
    {
        return $user->isKoordinator();
    }
}
