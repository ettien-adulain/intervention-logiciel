<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Requests\StoreRequeteRequest;
use App\Models\Client;
use App\Models\Requetes;
use App\Models\Utilisateurs;
use App\Support\RequeteActionsEnAttente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Interface web des requêtes (liste, création, fiche, pièces jointes…).
 */
class RequeteController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Requetes::class);

        $clients = $request->user()->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        return view('requetes.create', compact('clients'));
    }

    public function store(StoreRequeteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()->estSuperAdmin()) {
            $clientId = (int) $validated['client_id'];
        } else {
            $clientId = $request->user()->client_id;
            if ($clientId === null) {
                abort(403);
            }
        }

        $client = Client::query()->findOrFail($clientId);
        if (! $client->peutRecevoirNouvellesRequetes()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['client_id' => 'Cette entreprise est inactive : création de requête impossible.']);
        }

        $requete = Requetes::query()->create([
            'client_id' => $clientId,
            'user_id' => $request->user()->id,
            'technicien_id' => null,
            'titre' => $validated['titre'] ?? null,
            'description' => $validated['description'] ?? null,
            'urgence' => $validated['urgence'],
            'statut' => 'ouverte',
        ]);

        return redirect()
            ->route('requetes.show', $requete)
            ->with('status', 'requete_creee');
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Requetes::class);

        $query = Requetes::query()
            ->visiblesPour($request->user())
            ->with(['client', 'user', 'technicien'])
            ->withCount('medias');

        if ($request->user()->estSuperAdmin() && $request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        $requetes = $query->orderByDesc('id')->simplePaginate(20)->withQueryString();

        $clientsFiltre = $request->user()->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        $requetesActionsEnAttenteIds = array_flip(RequeteActionsEnAttente::idsPour($request->user()));

        return view('requetes.index', compact('requetes', 'clientsFiltre', 'requetesActionsEnAttenteIds'));
    }

    public function show(Request $request, Requetes $requete): View
    {
        $this->authorize('view', $requete);

        $requete->load([
            'client',
            'user',
            'technicien',
            'medias',
            'planifications.technicien',
            'validation',
            'intervention.technicien',
            'recu',
        ]);

        $techniciensPourPlanif = $request->user()->can('assignerTechnicien', $requete)
            ? Utilisateurs::query()
                ->where('role', RoleUtilisateur::Technicien)
                ->where('statut', 'actif')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get()
            : collect();

        $requeteFicheOpsAlerte = RequeteActionsEnAttente::pourCetteRequete($requete, $request->user());

        return view('requetes.show', compact('requete', 'techniciensPourPlanif', 'requeteFicheOpsAlerte'));
    }
}
