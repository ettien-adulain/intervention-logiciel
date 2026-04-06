{{--
    Tableau de bord — liens vers la phase 3 (clients) et prochaines phases.
--}}
@extends('layouts.app')

@section('title', 'Tableau de bord — ' . config('app.name'))

@section('content')
    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Tableau de bord</h1>
    <p style="color: #64748b; margin-bottom: 1.5rem;">
        Connecté en tant que <strong>{{ auth()->user()->role->label() }}</strong>.
    </p>

    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; margin: 0 0 1rem;">Accès rapide</h2>
        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.9375rem;">
            @can('viewAny', \App\Models\Client::class)
                <li style="margin-bottom: 0.5rem;"><a href="{{ route('clients.index') }}" style="color: var(--ycs-red, #c41e3a);">Gestion des entreprises clientes</a></li>
            @endcan
            @can('viewAny', \App\Models\Utilisateurs::class)
                <li style="margin-bottom: 0.5rem;"><a href="{{ route('utilisateurs.index') }}" style="color: var(--ycs-red, #c41e3a);">Comptes utilisateurs</a></li>
            @endcan
            @if(auth()->user()->client_id)
                <li><a href="{{ route('clients.show', auth()->user()->client_id) }}" style="color: var(--ycs-red, #c41e3a);">Fiche de mon entreprise</a></li>
            @endif
        </ul>
    </div>

    <div class="card">
        <p style="margin: 0 0 0.5rem;"><strong>Rôle</strong> : {{ auth()->user()->role->value }}</p>
        <p style="margin: 0 0 0.5rem;"><strong>Statut compte</strong> : {{ auth()->user()->statut }}</p>
        @if (auth()->user()->client_id)
            <p style="margin: 0;"><strong>Entreprise</strong> : client #{{ auth()->user()->client_id }}</p>
        @else
            <p style="margin: 0;"><strong>Entreprise</strong> : — (compte interne)</p>
        @endif
    </div>
@endsection
