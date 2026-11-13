<?php

namespace App\Policies;

use App\Models\AidNeed;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AidNeedPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isKoordinator();
    }

    public function view(User $user, AidNeed $aidNeed): bool
    {
        return $this->ownsShelter($user, $aidNeed);
    }

    public function create(User $user): bool
    {
        return $user->isKoordinator();
    }

    public function update(User $user, AidNeed $aidNeed): bool
    {
        return $this->ownsShelter($user, $aidNeed);
    }

    public function delete(User $user, AidNeed $aidNeed): bool
    {
        return $this->ownsShelter($user, $aidNeed);
    }

    private function ownsShelter(User $user, AidNeed $aidNeed): bool
    {
        return $user->isKoordinator()
            && DB::table('shelters')
                ->where('id', $aidNeed->shelter_id)
                ->where('coordinator_id', $user->id)
                ->exists();
    }
}
