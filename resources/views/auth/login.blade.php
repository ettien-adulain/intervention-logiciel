@extends('layouts.app')

@section('title', 'Connexion — ' . config('app.name'))

@section('content')
    <div class="card">
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <img src="{{ asset('images/logo-ycs.png') }}" alt="YAOCOM'S GROUPE — YCS" style="height: 52px; width: auto;">
        </div>
        <h1 style="font-size: 1.35rem; margin: 0 0 0.35rem; text-align: center; font-weight: 700;">Connexion</h1>
        <p class="muted text-sm" style="text-align: center; margin: 0 0 1.25rem;">Accédez à l’espace interventions</p>

        @if (session('status') === 'compte_inactif')
            <div class="alert alert-warn">Votre compte a été désactivé. Contactez l’administrateur.</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warn">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="form-field">
                <label class="field-label" for="email">Adresse e-mail</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            <div class="form-field">
                <label class="field-label" for="password">Mot de passe</label>
                <input class="input" id="password" type="password" name="password" required autocomplete="current-password">
            </div>
            <label class="text-sm" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; cursor: pointer;">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Se souvenir de moi
            </label>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
        </form>
    </div>
@endsection
