@extends('layouts.app')

@section('title', 'Clients — ' . config('app.name'))

@section('page_title', 'Entreprises clientes')
@section('page_subtitle', 'Gestion des comptes clients et de leur statut.')

@section('page_actions')
    <a href="{{ route('clients.create') }}" class="btn btn-primary">Nouveau client</a>
@endsection

@section('content')
    <div class="card card--flush">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th>Statut</th>
                        <th>Requêtes</th>
                        <th>Utilisateurs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td><strong>{{ $client->nom_entreprise }}</strong></td>
                            <td class="muted text-sm">
                                {{ $client->email ?? '—' }}<br>
                                {{ $client->telephone ?? '' }}
                            </td>
                            <td>
                                @if ($client->estActif())
                                    <span class="badge badge-ok">Actif</span>
                                @else
                                    <span class="badge badge-warn">Inactif</span>
                                @endif
                            </td>
                            <td>{{ $client->requetes_count }}</td>
                            <td>{{ $client->utilisateurs_count }}</td>
                            <td><a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-sm">Voir</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted" style="text-align: center; padding: 2.5rem 1rem;">Aucun client enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-nav">{{ $clients->links() }}</div>
@endsection
