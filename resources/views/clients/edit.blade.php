@extends('layouts.app')

@section('title', 'Modifier — ' . $client->nom_entreprise)

@section('content')
    <h1 style="font-size: 1.5rem; margin-bottom: 1rem;">Modifier la fiche client</h1>
    <p style="color: #64748b; margin-bottom: 1rem;">{{ $client->nom_entreprise }}</p>

    <div class="card" style="max-width: 36rem;">
        <form method="post" action="{{ route('clients.update', $client) }}">
            @csrf
            @method('PUT')
            @include('clients._form', ['client' => $client])
            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
@endsection
