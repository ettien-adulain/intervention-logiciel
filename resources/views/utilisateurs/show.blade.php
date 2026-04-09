@extends('layouts.app')

@section('title', $utilisateur->prenom . ' ' . $utilisateur->nom . ' — ' . config('app.name'))

@section('page_title', $utilisateur->prenom . ' ' . $utilisateur->nom)
@section('page_subtitle', $utilisateur->email)

@section('page_actions')
    <div class="inline-forms">
        @can('update', $utilisateur)
            <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="btn btn-primary btn-sm">Modifier</a>
        @endcan
        @can('viewAny', \App\Models\Utilisateurs::class)
            <a href="{{ route('utilisateurs.index') }}" class="btn btn-ghost btn-sm">Liste</a>
        @endcan
        @can('delete', $utilisateur)
            <form action="{{ route('utilisateurs.destroy', $utilisateur) }}" method="post" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm btn-danger-ghost">Supprimer</button>
            </form>
        @endcan
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">Détails du compte</h2>
        <p class="text-sm" style="margin: 0 0 1rem;">
            <span class="badge badge-muted">{{ $utilisateur->role->label() }}</span>
            @if($utilisateur->statut === 'actif')
                <span class="badge badge-ok">Actif</span>
            @else
                <span class="badge badge-warn">Inactif</span>
            @endif
        </p>
        <dl style="margin: 0; font-size: 0.875rem;">
            <div><dt class="muted" style="display: inline;">Entreprise :</dt>
                <dd style="display: inline; margin: 0;">{{ $utilisateur->client?->nom_entreprise ?? '—' }}</dd></div>
        </dl>
    </div>
@endsection
