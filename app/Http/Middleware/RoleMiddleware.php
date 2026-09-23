<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pembatas hak akses berbasis kolom `users.role`.
 *
 * Pemakaian pada route: `->middleware('role:admin')` atau `->middleware('role:admin,umkm')`.
 * Bentuk `role:admin|umkm` juga didukung agar cocok dengan penulisan di Rancangan.md.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = collect($roles)
            ->flatMap(fn (string $role) => explode('|', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $user = $request->user();

        // Tamu: arahkan ke login (atau 401 untuk request JSON/API).
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Silakan masuk terlebih dahulu.'], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->isActive()) {
            abort(403, 'Akun Anda sedang tidak aktif. Silakan hubungi administrator E-Wedu.');
        }

        if ($allowed !== [] && ! in_array($user->role, $allowed, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
