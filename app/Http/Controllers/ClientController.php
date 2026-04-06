<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD clients (phase 3 — CDC §4.1).
 *
 * Autorisations : `ClientPolicy` via `$this->authorize`.
 * - Index / création / suppression : super admin.
 * - Consultation / édition : super admin ou admin client de la même entreprise.
 */
class ClientController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->orderBy('nom_entreprise')
            ->withCount(['requetes', 'utilisateurs'])
            ->simplePaginate(15);

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::query()->create($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'client_cree');
    }

    public function show(Client $client): View
    {
        $this->authorize('view', $client);

        // Historique global (super admin) / aperçu métier : dernières requêtes + totaux.
        $client->loadCount(['requetes', 'utilisateurs']);
        $requetesRecentes = $client->requetes()
            ->orderByDesc('date_creation')
            ->limit(25)
            ->get();

        return view('clients.show', compact('client', 'requetesRecentes'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->safeForClient());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'client_mis_a_jour');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        // Attention : en base, suppression d’un client peut cascader sur `requetes` (FK).
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'client_supprime');
    }
}
