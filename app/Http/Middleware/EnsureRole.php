<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Contoh pemakaian di route: ->middleware('role:hr,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! in_array($user->role, $roles) && ! $user->isAdmin())) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        if (! $user->is_active) {
            abort(403, 'Akun Anda sedang dinonaktifkan. Hubungi Admin.');
        }

        return $next($request);
    }
}
