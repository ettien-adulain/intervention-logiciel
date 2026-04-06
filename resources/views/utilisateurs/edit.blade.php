@extends('layouts.app')

@section('title', 'Modifier — ' . $utilisateur->prenom . ' ' . $utilisateur->nom)

@section('content')
    <h1 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Modifier le compte</h1>
    <p style="color: #64748b; margin-bottom: 1rem;">{{ $utilisateur->email }}</p>

    <div class="card" style="max-width: 40rem;">
        <form method="post" action="{{ route('utilisateurs.update', $utilisateur) }}">
            @csrf
            @method('PUT')

            @if(auth()->id() === $utilisateur->id && auth()->user()->role === \App\Enums\RoleUtilisateur::ClientUser)
                @include('utilisateurs._form_profil', ['utilisateur' => $utilisateur])
            @else
                @include('utilisateurs._form_admin', ['utilisateur' => $utilisateur, 'clients' => $clients, 'mode' => 'edit'])
            @endif

            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('utilisateurs.show', $utilisateur) }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
@endsection
