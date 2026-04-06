<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    {{-- Logo source projet : logo-ycs.png → copié vers public/images/logo-ycs.png pour l’URL publique. --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --ycs-red: #c41e3a;
            --ycs-black: #0f172a;
            --ycs-bg: #f8fafc;
        }
        body { font-family: system-ui, -apple-system, sans-serif; margin: 0; background: var(--ycs-bg); color: var(--ycs-black); line-height: 1.5; }
        .wrap { max-width: 56rem; margin: 0 auto; padding: 1.5rem; }
        header.bar {
            background: #fff;
            border-bottom: 3px solid var(--ycs-red);
            padding: 0.65rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        header .brand { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit; }
        header .brand img { height: 40px; width: auto; display: block; }
        header .brand-text { font-weight: 700; font-size: 0.95rem; letter-spacing: 0.02em; }
        header .brand-sub { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.12em; color: #64748b; margin-top: 0.1rem; }
        nav.app-nav { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; }
        nav.app-nav a { color: #334155; text-decoration: none; font-size: 0.875rem; padding: 0.35rem 0.65rem; border-radius: 0.25rem; }
        nav.app-nav a:hover { background: #f1f5f9; color: var(--ycs-red); }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; text-decoration: none; cursor: pointer; border: none; font-family: inherit; }
        .btn-primary { background: var(--ycs-red); color: #fff; }
        .btn-primary:hover { filter: brightness(0.92); }
        .btn-ghost { background: transparent; color: #64748b; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem; }
        .alert-warn { background: #fef3c7; color: #92400e; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1.5rem; }
    </style>
</head>
<body>
    @auth
        <header class="bar">
            <a href="{{ route('dashboard') }}" class="brand">
                <img src="{{ asset('images/logo-ycs.png') }}" alt="YAOCOM'S GROUPE — YCS" width="120" height="40">
                <div>
                    <div class="brand-text">{{ config('app.name') }}</div>
                    <div class="brand-sub">Interventions</div>
                </div>
            </a>
            <nav class="app-nav">
                <a href="{{ route('dashboard') }}">Tableau de bord</a>
                @can('viewAny', \App\Models\Client::class)
                    <a href="{{ route('clients.index') }}">Clients</a>
                @endcan
                @can('viewAny', \App\Models\Utilisateurs::class)
                    <a href="{{ route('utilisateurs.index') }}">Utilisateurs</a>
                @endcan
                @if(auth()->user()->client_id)
                    <a href="{{ route('clients.show', auth()->user()->client_id) }}">Mon entreprise</a>
                @endif
            </nav>
            <form action="{{ route('logout') }}" method="post" style="margin: 0;">
                @csrf
                <span style="font-size: 0.8125rem; color: #64748b; margin-right: 0.75rem;">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
                <button type="submit" class="btn btn-ghost">Déconnexion</button>
            </form>
        </header>
    @endauth
    <main class="wrap">
        @yield('content')
    </main>
</body>
</html>
