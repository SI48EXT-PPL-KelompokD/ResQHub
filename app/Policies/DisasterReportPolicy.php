<?php

namespace App\Policies;

use App\Models\DisasterReport;
use App\Models\User;

class DisasterReportPolicy
{
    /**
     * Warga melihat laporan miliknya, admin melihat seluruh laporan.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isWarga();
    }

    public function view(User $user, DisasterReport $report): bool
    {
        return $user->isAdmin() || $report->reporter_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isWarga();
    }

    /**
     * Warga hanya dapat mengubah laporan miliknya yang masih menunggu verifikasi.
     */
    public function update(User $user, DisasterReport $report): bool
    {
        return $report->reporter_id === $user->id && $report->isPending();
    }

    public function delete(User $user, DisasterReport $report): bool
    {
        return $report->reporter_id === $user->id && $report->isPending();
    }

    public function verify(User $user, DisasterReport $report): bool
    {
        return $user->isAdmin() && $report->isPending();
    }
}
