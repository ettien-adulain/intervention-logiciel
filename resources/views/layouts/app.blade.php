<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @php
        $logoFavicon = public_path('images/logo-ycs.png');
        $logoFaviconUrl = asset('images/logo-ycs.png').(is_file($logoFavicon) ? '?v='.filemtime($logoFavicon) : '');
    @endphp
    <link rel="icon" href="{{ $logoFaviconUrl }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $logoFaviconUrl }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Si `npm run build` / `npm run dev` ne sont pas disponibles, styles métier dans public/css/app-ui.css --}}
        <link rel="stylesheet" href="{{ asset('css/app-ui.css') }}?v={{ is_file(public_path('css/app-ui.css')) ? filemtime(public_path('css/app-ui.css')) : 1 }}">
    @endif
</head>
@php
    $navActive = fn (string ...$patterns): string => request()->routeIs(...$patterns) ? 'is-active' : '';
@endphp
<body>
@if(auth()->check())
    <input type="checkbox" id="app-sidebar-open" class="sr-only" aria-hidden="true">
    <div class="app-shell">
        <label for="app-sidebar-open" class="app-sidebar-backdrop" aria-hidden="true"></label>
        <aside class="app-sidebar" aria-label="Navigation principale">
            <a href="{{ route('dashboard') }}" class="app-sidebar-brand">
                <img src="{{ asset('images/logo-ycs.png') }}" alt="" width="120" height="40">
                <div>
                    <div class="app-sidebar-brand-text">{{ config('app.name') }}</div>
                </div>
            </a>
            <nav class="app-sidebar-scroll">
                <div class="app-nav-section">
                    <div class="app-nav-label">Espace de travail</div>
                    <a href="{{ route('dashboard') }}" class="app-sidebar-link {{ $navActive('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        Tableau de bord
                    </a>
                    <a href="{{ route('requetes.index') }}" class="app-sidebar-link {{ $navActive('requetes.*') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        <span class="app-sidebar-link-text">Requêtes</span>
                        @if(($requetesActionsEnAttenteCount ?? 0) > 0)
                            <span class="sidebar-badge" title="Actions à traiter sur des tickets" aria-label="{{ $requetesActionsEnAttenteCount }} action(s) à traiter">{{ $requetesActionsEnAttenteCount > 99 ? '99+' : $requetesActionsEnAttenteCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('historique.requetes') }}" class="app-sidebar-link {{ $navActive('historique.requetes') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Historique
                    </a>
                    <a href="{{ route('reporting.index') }}" class="app-sidebar-link {{ $navActive('reporting.*') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        Reporting
                    </a>
                </div>
                @if(auth()->user()->can('viewAny', \App\Models\Client::class) || auth()->user()->can('viewAny', \App\Models\Utilisateurs::class))
                    <div class="app-nav-section">
                        <div class="app-nav-label">Administration</div>
                        @can('viewAny', \App\Models\Client::class)
                            <a href="{{ route('clients.index') }}" class="app-sidebar-link {{ $navActive('clients.*') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V3M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3.375 3h11.25c.621 0 1.125.504 1.125 1.125v9.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 012.25 13.5V4.125c0-.621.504-1.125 1.125-1.125z"/></svg>
                                Clients
                            </a>
                        @endcan
                        @can('viewAny', \App\Models\Utilisateurs::class)
                            <a href="{{ route('utilisateurs.index') }}" class="app-sidebar-link {{ $navActive('utilisateurs.*') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                Utilisateurs
                            </a>
                        @endcan
                    </div>
                @endif
                @if(auth()->user()->client_id)
                    <div class="app-nav-section">
                        <div class="app-nav-label">Mon compte</div>
                        <a href="{{ route('clients.show', auth()->user()->client_id) }}" class="app-sidebar-link {{ $navActive('clients.show') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                            Mon entreprise
                        </a>
                    </div>
                @endif
            </nav>
            <div class="app-sidebar-footer">
                @php
                    $u = auth()->user();
                    $initials = strtoupper(mb_substr($u->prenom, 0, 1).mb_substr($u->nom, 0, 1));
                @endphp
                <div class="app-user-pill">
                    <div class="app-user-avatar" aria-hidden="true">{{ $initials }}</div>
                    <div class="app-user-meta">
                        <div class="app-user-name">{{ $u->prenom }} {{ $u->nom }}</div>
                        <div class="app-user-role">{{ $u->role->label() }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-logout">Déconnexion</button>
                </form>
            </div>
        </aside>
        <div class="app-main">
            <header class="app-topbar">
                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                    <label for="app-sidebar-open" class="app-sidebar-toggle" aria-label="Ouvrir le menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </label>
                    <div style="min-width: 0;">
                        <h1>@yield('page_title', 'Espace connecté')</h1>
                        @hasSection('page_subtitle')
                            <p class="app-topbar-sub">@yield('page_subtitle')</p>
                        @endif
                    </div>
                </div>
                <div class="page-toolbar" style="margin: 0;">
                    @yield('page_actions')
                </div>
            </header>
            <main class="app-content">
                @include('partials.flash-status')
                @yield('content')
            </main>
        </div>
    </div>
@else
    <div class="app-guest-body">
        <div class="app-guest-card">
            @yield('content')
        </div>
    </div>
@endif
</body>
</html>
