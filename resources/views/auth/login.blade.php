{{--
    Connexion — phase 2 (CDC).
    Champs : email, password, remember (case « Se souvenir de moi »).
--}}
@extends('layouts.app')

@section('title', 'Connexion — ' . config('app.name'))

@section('content')
    <div class="card" style="max-width: 24rem; margin: 2rem auto;">
        {{-- Même logo que l’espace connecté : fichier public/images/logo-ycs.png --}}
        <div style="text-align: center; margin-bottom: 1.25rem;">
            <img src="{{ asset('images/logo-ycs.png') }}" alt="YAOCOM'S GROUPE — YCS" style="height: 48px; width: auto;">
        </div>
        <h1 style="font-size: 1.25rem; margin: 0 0 1rem; text-align: center;">Connexion</h1>

        @if (session('status') === 'compte_inactif')
            <div class="alert alert-warn">
                Votre compte a été désactivé. Contactez l’administrateur.
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warn">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="email" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="password" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Mot de passe</label>
                <input id="password" type="password" name="password" required
                    style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.375rem; box-sizing: border-box;">
            </div>
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem;">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Se souvenir de moi
            </label>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
        </form>
    </div>
@endsection
