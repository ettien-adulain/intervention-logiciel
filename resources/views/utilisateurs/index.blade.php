@extends('layouts.app')

@section('title', 'Utilisateurs — ' . config('app.name'))

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem; margin: 0;">Comptes utilisateurs</h1>
        @can('create', \App\Models\Utilisateurs::class)
            <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary">Nouvel utilisateur</a>
        @endcan
    </div>

    @if (session('status') === 'utilisateur_supprime')
        <div class="alert" style="background: #fee2e2; color: #991b1b;">Utilisateur supprimé.</div>
    @endif
    @if (session('status') === 'utilisateur_cree')
        <div class="alert" style="background: #dcfce7; color: #166534;">Utilisateur créé.</div>
    @endif

    @if(auth()->user()->estSuperAdmin() && $clientsFiltre->isNotEmpty())
        <form method="get" action="{{ route('utilisateurs.index') }}" class="card" style="padding: 1rem; margin-bottom: 1rem; display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label for="client_id" style="font-size: 0.875rem; display: block; margin-bottom: 0.25rem;">Filtrer par entreprise</label>
                <select name="client_id" id="client_id" style="padding: 0.5rem; min-width: 14rem;">
                    <option value="">Toutes</option>
                    @foreach($clientsFiltre as $cl)
                        <option value="{{ $cl->id }}" @selected(request('client_id') == $cl->id)>{{ $cl->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
    @endif

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="background: #f1f5f9; text-align: left;">
                    <th style="padding: 0.75rem 1rem;">Nom</th>
                    <th style="padding: 0.75rem 1rem;">E-mail</th>
                    <th style="padding: 0.75rem 1rem;">Rôle</th>
                    <th style="padding: 0.75rem 1rem;">Entreprise</th>
                    <th style="padding: 0.75rem 1rem;">Statut</th>
                    <th style="padding: 0.75rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($utilisateurs as $u)
                    <tr style="border-top: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem 1rem;">{{ $u->prenom }} {{ $u->nom }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $u->email }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $u->role->label() }}</td>
                        <td style="padding: 0.75rem 1rem; color: #64748b;">{{ $u->client?->nom_entreprise ?? '—' }}</td>
                        <td style="padding: 0.75rem 1rem;">{{ $u->statut }}</td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="{{ route('utilisateurs.show', $u) }}" class="btn btn-ghost" style="padding: 0.35rem 0.75rem;">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem 1rem; text-align: center; color: #64748b;">Aucun utilisateur.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 1rem;">{{ $utilisateurs->links() }}</div>
@endsection
