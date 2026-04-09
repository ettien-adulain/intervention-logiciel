@extends('layouts.app')

@section('title', 'Nouveau client — ' . config('app.name'))

@section('page_title', 'Nouvelle entreprise cliente')

@section('content')
    <div class="card" style="max-width: 36rem;">
        <form method="post" action="{{ route('clients.store') }}">
            @csrf
            @include('clients._form', ['client' => null])
            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('clients.index') }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
@endsection
