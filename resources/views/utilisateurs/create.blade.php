@extends('layouts.app')

@section('title', 'Nouvel utilisateur — ' . config('app.name'))

@section('content')
    <h1 style="font-size: 1.5rem; margin-bottom: 1rem;">Nouveau compte</h1>
    <p style="color: #64748b; margin-bottom: 1rem; font-size: 0.875rem;">
        Super administrateur : tous les rôles. Administrateur client : uniquement des comptes pour son entreprise.
    </p>

    <div class="card" style="max-width: 40rem;">
        <form method="post" action="{{ route('utilisateurs.store') }}">
            @csrf
            @include('utilisateurs._form_admin', ['utilisateur' => new \App\Models\Utilisateurs(['statut' => 'actif']), 'clients' => $clients, 'mode' => 'create'])
            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Créer</button>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
@endsection
