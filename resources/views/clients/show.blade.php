@extends('layouts.app')

@section('title', $client->nom_entreprise . ' — ' . config('app.name'))

@section('page_title', $client->nom_entreprise)
@section('page_subtitle')
    @if ($client->estActif())
        <span class="badge badge-ok">Actif</span>
    @else
        <span class="badge badge-warn">Inactif — nouvelles requêtes bloquées</span>
    @endif
@endsection

@section('page_actions')
    <div class="inline-forms">
        @can('update', $client)
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary btn-sm">Modifier</a>
        @endcan
        @can('viewAny', \App\Models\Client::class)
            <a href="{{ route('clients.index') }}" class="btn btn-ghost btn-sm">Liste des clients</a>
        @endcan
        @can('viewAny', \App\Models\Utilisateurs::class)
            <a href="{{ route('utilisateurs.index', ['client_id' => $client->id]) }}" class="btn btn-ghost btn-sm">Utilisateurs</a>
        @endcan
        <a href="{{ route('requetes.index', ['client_id' => $client->id]) }}" class="btn btn-ghost btn-sm">Requêtes</a>
        @can('delete', $client)
            <form action="{{ route('clients.destroy', $client) }}" method="post" onsubmit="return confirm('Supprimer définitivement cette entreprise et les données liées (requêtes en cascade selon la BDD) ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm btn-danger-ghost">Supprimer</button>
            </form>
        @endcan
    </div>
@endsection

@section('content')
    @if (session('status') === 'client_cree')
        <div class="alert alert-success">Entreprise créée.</div>
    @endif
    @if (session('status') === 'client_mis_a_jour')
        <div class="alert alert-success">Fiche mise à jour.</div>
    @endif

    <div class="card" style="margin-bottom: 1.25rem;">
        <h2 class="card-title">Coordonnées</h2>
        <dl style="display: grid; gap: 0.5rem; margin: 0; font-size: 0.875rem;">
            <div><dt class="muted" style="display: inline;">E-mail :</dt> <dd style="display: inline; margin: 0;">{{ $client->email ?? '—' }}</dd></div>
            <div><dt class="muted" style="display: inline;">Téléphone :</dt> <dd style="display: inline; margin: 0;">{{ $client->telephone ?? '—' }}</dd></div>
            <div><dt class="muted" style="margin-bottom: 0.25rem;">Adresse :</dt> <dd style="margin: 0; white-space: pre-wrap;">{{ $client->adresse ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="card" style="margin-bottom: 1.25rem;">
        <h2 class="card-title">Synthèse</h2>
        <p class="text-sm" style="margin: 0;">
            <strong>{{ $client->requetes_count }}</strong> requête(s) ·
            <strong>{{ $client->utilisateurs_count }}</strong> utilisateur(s) rattaché(s)
        </p>
    </div>

    <div class="card card--flush">
        <h2 class="card-title" style="padding: 1rem 1.25rem 0;">Dernières requêtes (aperçu)</h2>
        <p class="card-sub" style="padding: 0 1.25rem 1rem;">Les 25 plus récentes.</p>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Statut</th>
                        <th>Urgence</th>
                        <th>Création</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requetesRecentes as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->titre ?? '—' }}</td>
                            <td><span class="badge badge-muted">{{ $req->statut }}</span></td>
                            <td>{{ $req->urgence }}</td>
                            <td class="muted text-sm">{{ $req->date_creation?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted" style="text-align: center; padding: 2rem 1rem;">Aucune requête pour ce client.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
