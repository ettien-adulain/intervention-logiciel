<?php

namespace App\Support;

use App\Models\Log;
use Illuminate\Http\Request;

/**
 * Écriture centralisée dans `logs` (phase 13 — CDC §5 / §6).
 */
final class Journalisation
{
    public static function trace(Request $request, string $action, string $description): void
    {
        Log::query()->create([
            'user_id' => $request->user()?->getAuthIdentifier(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
        ]);
    }

    /** Événement sans session (ex. tentative de connexion invalide). */
    public static function traceSansUtilisateur(?string $ip, string $action, string $description): void
    {
        Log::query()->create([
            'user_id' => null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
        ]);
    }
}
