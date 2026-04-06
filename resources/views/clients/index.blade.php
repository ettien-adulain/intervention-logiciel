@extends('layouts.app')

@section('title', 'Clients — ' . config('app.name'))

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem; margin: 0;">Entreprises clientes</h1>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">Nouveau client</a>
    </div>

    @if (session('status') === 'client_supprime')
        <div class="alert" style="background: #dcfce7; color: #166534;">L’entreprise a été supprimée.</div>
    @endif

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left;">
                    <th style="padding: 0.75rem 1rem;">Entreprise</th>
                    <th style="padding: 0.75rem 1rem;">Contact</th>
                    <th style="padding: 0.75rem 1rem;">Statut</th>
                    <th style="padding: 0.75rem 1rem;">Requêtes</th>
                    <th style="padding: 0.75rem 1rem;">Utilisateurs</th>
                    <th style="padding: 0.75rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr style="border-top: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem;">
                            <strong>{{ $client->nom_entreprise }}</strong>
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #64748b;">
                            {{ $client->email ?? '—' }}<br>
                            <span style="font-size: 0.8125rem;">{{ $client->telephone ?? '' }}</span>
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if ($client->estActif())
                                <span style="color: #166534; font-weight: 600;">Actif</span>
                            @else
                                <span style="color: #b91c1c; font-weight: 600;">Inactif</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem;">{{ $client->requetes_count }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $client->utilisateurs_count }}</td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost" style="padding: 0.35rem 0.75rem;">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem 1rem; text-align: center; color: #64748b;">Aucun client enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        {{ $clients->links() }}
    </div>
@endsection
