<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Requetes;
use Illuminate\View\View;

/**
 * Tableau de bord après connexion : indicateurs et accès rapides selon le périmètre utilisateur.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $base = Requetes::query()->visiblesPour($user);

        $stats = [
            'requetes_total' => (clone $base)->count(),
            'requetes_actives' => (clone $base)->whereIn('statut', [
                'ouverte', 'en_attente', 'planifiee', 'en_cours',
            ])->count(),
            'requetes_cloture' => (clone $base)->whereIn('statut', ['terminee', 'cloturee'])->count(),
        ];

        $clientNom = null;
        if ($user->client_id) {
            $clientNom = Client::query()->whereKey($user->client_id)->value('nom_entreprise');
        }

        return view('dashboard', compact('stats', 'clientNom'));
    }
}
