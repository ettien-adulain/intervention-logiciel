@extends('layouts.app')

@section('title', 'Utilisateurs — ' . config('app.name'))

@section('page_title', 'Comptes utilisateurs')
@section('page_subtitle', 'Création et gestion des accès selon les droits du cahier des charges.')

@section('page_actions')
    @can('create', \App\Models\Utilisateurs::class)
        <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary">Nouvel utilisateur</a>
    @endcan
@endsection

@section('content')
    @if (session('status') === 'utilisateur_supprime')
        <div class="alert alert-danger">Utilisateur supprimé.</div>
    @endif
    @if (session('status') === 'utilisateur_cree')
        <div class="alert alert-success">Utilisateur créé.</div>
    @endif

    @if(auth()->user()->estSuperAdmin() && $clientsFiltre->isNotEmpty())
        <form method="get" action="{{ route('utilisateurs.index') }}" class="card" style="margin-bottom: 1.25rem;">
            <div class="form-grid">
                <div>
                    <label class="field-label" for="client_id">Filtrer par entreprise</label>
                    <select class="input" name="client_id" id="client_id" style="max-width: 20rem;">
                        <option value="">Toutes</option>
                        @foreach($clientsFiltre as $cl)
                            <option value="{{ $cl->id }}" @selected(request('client_id') == $cl->id)>{{ $cl->nom_entreprise }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </div>
        </form>
    @endif

    <div class="card card--flush">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Entreprise</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($utilisateurs as $utilisateur)
                        <tr>
                            <td><strong>{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</strong></td>
                            <td class="muted">{{ $utilisateur->email }}</td>
                            <td class="muted text-sm">{{ $utilisateur->client?->nom_entreprise ?? '—' }}</td>
                            <td><span class="badge badge-muted">{{ $utilisateur->role->label() }}</span></td>
                            <td>
                                @if($utilisateur->statut === 'actif')
                                    <span class="badge badge-ok">Actif</span>
                                @else
                                    <span class="badge badge-warn">Inactif</span>
                                @endif
                            </td>
                            <td><a href="{{ route('utilisateurs.show', $utilisateur) }}" class="btn btn-ghost btn-sm">Voir</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted" style="text-align: center; padding: 2.5rem 1rem;">Aucun utilisateur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-nav">{{ $utilisateurs->links() }}</div>
@endsection
