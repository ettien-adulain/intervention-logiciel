<?php

namespace App\Http\Middleware;

use App\Enums\RoleUtilisateur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à un ou plusieurs rôles (OU logique).
 *
 * Exemples de routes (web.php) :
 *   ->middleware('role:super_admin')
 *   ->middleware('role:super_admin,client_admin')   // l’un ou l’autre
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$rolesFlat): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        // Laravel peut passer "super_admin,client_admin" en un seul argument.
        $allowed = [];
        foreach ($rolesFlat as $chunk) {
            foreach (explode(',', $chunk) as $r) {
                $r = trim($r);
                if ($r !== '') {
                    $allowed[] = $r;
                }
            }
        }

        foreach ($allowed as $roleString) {
            if (! RoleUtilisateur::tryFrom($roleString)) {
                abort(500, 'Rôle inconnu dans le middleware : '.$roleString);
            }
        }

        $current = $user->role instanceof RoleUtilisateur
            ? $user->role->value
            : (string) $user->role;

        if (in_array($current, $allowed, true)) {
            return $next($request);
        }

        abort(403);
    }
}
