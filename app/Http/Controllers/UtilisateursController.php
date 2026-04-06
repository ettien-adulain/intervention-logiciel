<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Requests\StoreUtilisateurRequest;
use App\Http\Requests\UpdateUtilisateurRequest;
use App\Models\Client;
use App\Models\Utilisateurs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 4 — CDC §4.2 : CRUD comptes `utilisateurs`.
 *
 * - Super admin : tous les rôles, tous les clients (ou sans client pour super admin / technicien).
 * - Admin client : uniquement `client_admin` et `client_user` rattachés à son entreprise.
 */
class UtilisateursController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Utilisateurs::class);

        $user = $request->user();
        $query = Utilisateurs::query()
            ->with('client')
            ->orderBy('nom')
            ->orderBy('prenom');

        if ($user->estSuperAdmin() && $request->filled('client_id')) {
            $query->where('client_id', (int) $request->query('client_id'));
        }

        if ($user->role === RoleUtilisateur::ClientAdmin && $user->client_id) {
            $query->where('client_id', $user->client_id);
        }

        $utilisateurs = $query->simplePaginate(20)->withQueryString();

        $clientsFiltre = $user->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        return view('utilisateurs.index', compact('utilisateurs', 'clientsFiltre'));
    }

    public function create(): View
    {
        $this->authorize('create', Utilisateurs::class);

        $clients = request()->user()->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        return view('utilisateurs.create', compact('clients'));
    }

    public function store(StoreUtilisateurRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);
        Utilisateurs::query()->create($data);

        return redirect()
            ->route('utilisateurs.index')
            ->with('status', 'utilisateur_cree');
    }

    public function show(Utilisateurs $utilisateur): View
    {
        $this->authorize('view', $utilisateur);
        $utilisateur->load('client');

        return view('utilisateurs.show', compact('utilisateur'));
    }

    public function edit(Utilisateurs $utilisateur): View
    {
        $this->authorize('update', $utilisateur);

        $clients = request()->user()->estSuperAdmin()
            ? Client::query()->orderBy('nom_entreprise')->get()
            : collect();

        return view('utilisateurs.edit', compact('utilisateur', 'clients'));
    }

    public function update(UpdateUtilisateurRequest $request, Utilisateurs $utilisateur): RedirectResponse
    {
        $utilisateur->update($request->donneesSaufMotDePasseVide());

        return redirect()
            ->route('utilisateurs.show', $utilisateur)
            ->with('status', 'utilisateur_mis_a_jour');
    }

    public function destroy(Utilisateurs $utilisateur): RedirectResponse
    {
        $this->authorize('delete', $utilisateur);

        $utilisateur->delete();

        return redirect()
            ->route('utilisateurs.index')
            ->with('status', 'utilisateur_supprime');
    }
}
