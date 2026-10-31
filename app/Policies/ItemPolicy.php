<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
