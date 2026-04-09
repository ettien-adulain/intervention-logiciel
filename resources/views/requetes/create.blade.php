@extends('layouts.app')

@section('title', 'Nouvelle requête — ' . config('app.name'))

@section('page_title', 'Nouvelle requête')
@section('page_subtitle', 'Le ticket sera créé au statut « ouverte ». La planification interviendra ensuite dans l’application.')

@section('page_actions')
    <a href="{{ route('requetes.index') }}" class="btn btn-ghost btn-sm">← Liste des requêtes</a>
@endsection

@section('content')
    <div class="card" style="max-width: 40rem;">
        <form method="post" action="{{ route('requetes.store') }}">
            @csrf

            @if(auth()->user()->estSuperAdmin())
                <div class="form-field">
                    <label class="field-label" for="client_id">Entreprise cliente</label>
                    <select class="input" name="client_id" id="client_id" required>
                        <option value="">— Choisir —</option>
                        @foreach($clients as $cl)
                            <option value="{{ $cl->id }}" @selected(old('client_id') == $cl->id)>{{ $cl->nom_entreprise }}@if(!$cl->estActif()) (inactif)@endif</option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="form-field">
                <label class="field-label" for="titre">Titre (optionnel)</label>
                <input class="input" type="text" name="titre" id="titre" value="{{ old('titre') }}" maxlength="255">
                @error('titre')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label class="field-label" for="description">Description</label>
                <textarea class="input" name="description" id="description" rows="5">{{ old('description') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field">
                <label class="field-label" for="urgence">Urgence</label>
                <select class="input" name="urgence" id="urgence" required style="max-width: 16rem;">
                    <option value="faible" @selected(old('urgence') === 'faible')>Faible</option>
                    <option value="moyenne" @selected(old('urgence', 'moyenne') === 'moyenne')>Moyenne</option>
                    <option value="elevee" @selected(old('urgence') === 'elevee')>Élevée</option>
                </select>
                @error('urgence')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="inline-forms" style="margin-top: 1.25rem;">
                <button type="submit" class="btn btn-primary">Créer la requête</button>
                <a href="{{ route('requetes.index') }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </div>
@endsection
