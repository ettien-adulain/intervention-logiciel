@extends('layouts.app')

@section('title', $client->nom_entreprise . ' — ' . config('app.name'))

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; margin: 0 0 0.25rem;">{{ $client->nom_entreprise }}</h1>
            @if ($client->estActif())
                <span style="color: #166534; font-weight: 600; font-size: 0.875rem;">Actif</span>
            @else
                <span style="color: #b91c1c; font-weight: 600; font-size: 0.875rem;">Inactif — nouvelles requêtes bloquées</span>
            @endif
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Modifier</a>
            @endcan
            @can('viewAny', \App\Models\Client::class)
                <a href="{{ route('clients.index') }}" class="btn btn-ghost">Liste des clients</a>
            @endcan
            @can('viewAny', \App\Models\Utilisateurs::class)
                <a href="{{ route('utilisateurs.index', ['client_id' => $client->id]) }}" class="btn btn-ghost">Utilisateurs</a>
            @endcan
            @can('delete', $client)
                <form action="{{ route('clients.destroy', $client) }}" method="post" onsubmit="return confirm('Supprimer définitivement cette entreprise et les données liées (requêtes en cascade selon la BDD) ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost" style="color: #b91c1c;">Supprimer</button>
                </form>
            @endcan
        </div>
    </div>

    @if (session('status') === 'client_cree')
        <div class="alert" style="background: #dcfce7; color: #166534;">Entreprise créée.</div>
    @endif
    @if (session('status') === 'client_mis_a_jour')
        <div class="alert" style="background: #dcfce7; color: #166534;">Fiche mise à jour.</div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; margin: 0 0 1rem;">Coordonnées</h2>
        <dl style="display: grid; gap: 0.5rem; margin: 0; font-size: 0.875rem;">
            <div><dt style="display: inline; color: #64748b;">E-mail :</dt> <dd style="display: inline; margin: 0;">{{ $client->email ?? '—' }}</dd></div>
            <div><dt style="display: inline; color: #64748b;">Téléphone :</dt> <dd style="display: inline; margin: 0;">{{ $client->telephone ?? '—' }}</dd></div>
            <div><dt style="color: #64748b; margin-bottom: 0.25rem;">Adresse :</dt> <dd style="margin: 0; white-space: pre-wrap;">{{ $client->adresse ?? '—' }}</dd></div>
        </dl>
    </div>

    {{-- Synthèse « historique global » (CDC §4.1) : volumes par client pour le super admin / lecture métier. --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; margin: 0 0 1rem;">Synthèse</h2>
        <p style="margin: 0; font-size: 0.875rem;">
            <strong>{{ $client->requetes_count }}</strong> requête(s) ·
            <strong>{{ $client->utilisateurs_count }}</strong> utilisateur(s) rattaché(s)
        </p>
    </div>

    <div class="card" style="padding: 0; overflow-x: auto;">
        <h2 style="font-size: 1rem; margin: 0; padding: 1rem 1rem 0;">Dernières requêtes (aperçu)</h2>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0; padding: 0.25rem 1rem 1rem;">Les 25 plus récentes — liste complète à brancher en phase 5.</p>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left;">
                    <th style="padding: 0.75rem 1rem;">#</th>
                    <th style="padding: 0.75rem 1rem;">Titre</th>
                    <th style="padding: 0.75rem 1rem;">Statut</th>
                    <th style="padding: 0.75rem 1rem;">Urgence</th>
                    <th style="padding: 0.75rem 1rem;">Création</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requetesRecentes as $req)
                    <tr style="border-top: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem;">{{ $req->id }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $req->titre ?? '—' }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $req->statut }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $req->urgence }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $req->date_creation?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 1.5rem 1rem; text-align: center; color: #64748b;">Aucune requête pour ce client.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
