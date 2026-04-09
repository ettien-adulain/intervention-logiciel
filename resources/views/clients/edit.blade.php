@extends('layouts.app')

@section('title', 'Modifier — ' . $client->nom_entreprise)

@section('page_title', 'Modifier la fiche client')
@section('page_subtitle', $client->nom_entreprise)

@section('content')
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
