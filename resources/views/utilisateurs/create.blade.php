@extends('layouts.app')

@section('title', 'Nouvel utilisateur — ' . config('app.name'))

@section('page_title', 'Nouveau compte')
@section('page_subtitle', 'Super administrateur : tous les rôles. Administrateur client : comptes pour son entreprise uniquement.')

@section('content')
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
