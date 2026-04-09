@extends('layouts.app')

@section('title', 'Tableau de bord — ' . config('app.name'))

@section('page_title', 'Tableau de bord')
@section('page_subtitle')
    Bonjour {{ auth()->user()->prenom }} — vue d’ensemble de votre activité interventions.
@endsection

@section('content')
    @if(($requetesActionsEnAttenteCount ?? 0) > 0)
        <div class="alert alert-warn dashboard-pending-banner" role="status">
            <strong>Actions en attente.</strong>
            {{ $requetesActionsEnAttenteCount }} ticket(s) nécessitent votre attention — ouvrez la liste
            <a href="{{ route('requetes.index') }}" class="link-inline">Requêtes</a>.
        </div>
    @endif
    <div class="tabs-radios">
        <input type="radio" name="dash-tabs" id="dash-tab-overview" class="tab-input" checked>
        <input type="radio" name="dash-tabs" id="dash-tab-shortcuts" class="tab-input">
        <input type="radio" name="dash-tabs" id="dash-tab-account" class="tab-input">

        <div class="tab-bar" role="tablist" aria-label="Sections du tableau de bord">
            <label for="dash-tab-overview" role="tab">Vue d’ensemble</label>
            <label for="dash-tab-shortcuts" role="tab">Accès rapide</label>
            <label for="dash-tab-account" role="tab">Mon compte</label>
        </div>

        <div class="tab-panel panel-overview">
            <div class="stat-grid" style="margin-bottom: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-card-label">Requêtes (périmètre)</div>
                    <div class="stat-card-value">{{ $stats['requetes_total'] }}</div>
                    <div class="stat-card-hint">Tickets visibles pour votre rôle</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">En cours de traitement</div>
                    <div class="stat-card-value">{{ $stats['requetes_actives'] }}</div>
                    <div class="stat-card-hint">Non terminées / non clôturées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">Terminées ou clôturées</div>
                    <div class="stat-card-value">{{ $stats['requetes_cloture'] }}</div>
                    <div class="stat-card-hint">Historique résolu</div>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title">Activité</h2>
                <p class="card-sub" style="margin-bottom: 0;">
                    Consultez la <a href="{{ route('requetes.index') }}" style="color: var(--app-accent); font-weight: 600;">liste des requêtes</a>
                    pour agir sur les tickets, ou l’
                    <a href="{{ route('historique.requetes') }}" style="color: var(--app-accent); font-weight: 600;">historique</a>
                    pour des recherches avancées et la traçabilité.
                </p>
            </div>
        </div>

        <div class="tab-panel panel-shortcuts">
            <div class="quick-links">
                @can('viewAny', \App\Models\Client::class)
                    <a href="{{ route('clients.index') }}" class="quick-link">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V3M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3.375 3h11.25c.621 0 1.125.504 1.125 1.125v9.375c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 012.25 13.5V4.125c0-.621.504-1.125 1.125-1.125z"/></svg>
                        </span>
                        Entreprises clientes
                    </a>
                @endcan
                @can('viewAny', \App\Models\Utilisateurs::class)
                    <a href="{{ route('utilisateurs.index') }}" class="quick-link">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        </span>
                        Comptes utilisateurs
                    </a>
                @endcan
                @can('create', \App\Models\Requetes::class)
                    <a href="{{ route('requetes.create') }}" class="quick-link">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </span>
                        Nouvelle requête
                    </a>
                @endcan
                <a href="{{ route('requetes.index') }}" class="quick-link">
                    <span class="quick-link-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    </span>
                    Requêtes &amp; pièces jointes
                </a>
                <a href="{{ route('reporting.index') }}" class="quick-link">
                    <span class="quick-link-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    </span>
                    Reporting &amp; statistiques
                </a>
                @if(auth()->user()->client_id)
                    <a href="{{ route('clients.show', auth()->user()->client_id) }}" class="quick-link">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        </span>
                        Fiche entreprise
                    </a>
                @endif
            </div>
        </div>

        <div class="tab-panel panel-account">
            <div class="card" style="max-width: 32rem;">
                <h2 class="card-title">Profil connecté</h2>
                <dl style="margin: 0; font-size: 0.875rem; display: grid; gap: 0.65rem;">
                    <div><strong>Rôle</strong> — {{ auth()->user()->role->label() }} <span class="muted">({{ auth()->user()->role->value }})</span></div>
                    <div><strong>Statut compte</strong> —
                        @if(auth()->user()->statut === 'actif')
                            <span class="badge badge-ok">Actif</span>
                        @else
                            <span class="badge badge-warn">Inactif</span>
                        @endif
                    </div>
                    <div>
                        <strong>Entreprise</strong> —
                        @if (auth()->user()->client_id)
                            {{ $clientNom ?? 'Client #'.auth()->user()->client_id }}
                        @else
                            <span class="muted">Compte interne (sans rattachement client)</span>
                        @endif
                    </div>
                    <div><strong>E-mail</strong> — {{ auth()->user()->email }}</div>
                </dl>
            </div>
        </div>
    </div>
@endsection
