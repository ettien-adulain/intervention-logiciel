<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 2 CDC : refuser l’accès si `utilisateurs.statut` ≠ `actif`.
 *
 * À placer après `auth` : ainsi un compte désactivé pendant une session
 * est déconnecté au prochain chargement de page.
 */
class EnsureCompteActif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // `auth` a déjà garanti une session ; sécurité défensive.
        if ($user === null) {
            return $next($request);
        }

        if (! $user->estActif()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'compte_inactif');
        }

        return $next($request);
    }
}
