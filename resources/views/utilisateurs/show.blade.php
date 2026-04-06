@extends('layouts.app')

@section('title', $utilisateur->prenom . ' ' . $utilisateur->nom)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; margin: 0 0 0.25rem;">{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</h1>
            <p style="margin: 0; color: #64748b;">{{ $utilisateur->email }}</p>
            <p style="margin: 0.5rem 0 0; font-size: 0.875rem;"><strong>{{ $utilisateur->role->label() }}</strong> · {{ $utilisateur->statut }}</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @can('update', $utilisateur)
                <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="btn btn-primary">Modifier</a>
            @endcan
            @can('viewAny', \App\Models\Utilisateurs::class)
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-ghost">Liste</a>
            @endcan
            @can('delete', $utilisateur)
                <form action="{{ route('utilisateurs.destroy', $utilisateur) }}" method="post" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost" style="color: #b91c1c;">Supprimer</button>
                </form>
            @endcan
        </div>
    </div>

    @if (session('status') === 'utilisateur_mis_a_jour')
        <div class="alert" style="background: #dcfce7; color: #166534;">Modifications enregistrées.</div>
    @endif

    <div class="card">
        <h2 style="font-size: 1rem; margin: 0 0 1rem;">Détails</h2>
        <dl style="margin: 0; font-size: 0.875rem;">
            <div style="margin-bottom: 0.5rem;"><dt style="display: inline; color: #64748b;">Entreprise :</dt>
                <dd style="display: inline; margin: 0;">{{ $utilisateur->client?->nom_entreprise ?? '—' }}</dd></div>
        </dl>
    </div>
@endsection
