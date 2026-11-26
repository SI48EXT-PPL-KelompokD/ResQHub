<?php

namespace App\Support;

use App\Models\User;

/**
 * Setelah masuk atau mendaftar, pengguna kembali ke halaman tujuan hanya jika
 * perannya boleh membuka halaman tersebut. Selain itu diarahkan ke dashboard.
 */
class IntendedRedirect
{
    public static function forgetIfNotAllowed(User $user): void
    {
        $intended = (string) session('url.intended');

        $blocked = (str_contains($intended, '/laporan') && ! $user->isWarga())
            || (str_contains($intended, '/donor/') && ! $user->isDonatur());

        if ($blocked) {
            session()->forget('url.intended');
        }
    }
}
