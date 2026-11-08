<?php

namespace App\Policies;

use App\Models\EarlyWarning;
use App\Models\User;

class EarlyWarningPolicy
{
    /**
     * Pengelolaan peringatan dini hanya untuk Admin Pusdalops.
     * Banner peringatan aktif untuk warga dibaca lewat service tanpa policy.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, EarlyWarning $warning): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, EarlyWarning $warning): bool
    {
        return $user->isAdmin() && ($warning->isDraft() || $warning->isActive());
    }

    public function publish(User $user, EarlyWarning $warning): bool
    {
        return $user->isAdmin() && $warning->isDraft();
    }

    /**
     * Hanya draf yang boleh dihapus permanen.
     */
    public function delete(User $user, EarlyWarning $warning): bool
    {
        return $user->isAdmin() && $warning->isDraft();
    }

    /**
     * Peringatan aktif dicabut, bukan dihapus.
     */
    public function revoke(User $user, EarlyWarning $warning): bool
    {
        return $user->isAdmin() && $warning->isActive();
    }
}
