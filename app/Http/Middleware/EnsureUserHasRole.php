<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Batasi akses route berdasarkan peran pengguna.
     * Contoh: ->middleware('role:admin') atau ->middleware('role:admin,koordinator')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Akses Ditolak: Peran Anda tidak memiliki wewenang untuk halaman ini.');
        }

        return $next($request);
    }
}
