@extends('layouts.app')

@section('title', 'Reporting — ' . config('app.name'))

@section('page_title', 'Reporting & statistiques')
@section('page_subtitle', 'Indicateurs sur la période choisie, selon votre périmètre d’accès.')

@section('content')
    <form method="get" class="card" style="margin-bottom: 1.25rem;">
        <div class="inline-forms" style="flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label class="field-label" for="date_debut">Du</label>
                <input class="input" type="date" name="date_debut" id="date_debut" value="{{ $debut->format('Y-m-d') }}">
            </div>
            <div>
                <label class="field-label" for="date_fin">Au</label>
                <input class="input" type="date" name="date_fin" id="date_fin" value="{{ $fin->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary">Actualiser</button>
            <a href="{{ route('reporting.index') }}" class="btn btn-ghost">30 derniers jours</a>
        </div>
    </form>

    <div class="stat-grid" style="margin-bottom: 1.25rem;">
        <div class="stat-card">
            <div class="stat-card-label">Requêtes créées</div>
            <div class="stat-card-value">{{ $stats['requetes_creees'] }}</div>
            <div class="stat-card-hint">Sur la période</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Interventions terminées</div>
            <div class="stat-card-value">{{ $stats['interventions_terminees'] }}</div>
            <div class="stat-card-hint">Heure de fin dans la période</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Temps moyen de résolution</div>
            <div class="stat-card-value">
                @if($stats['temps_moyen_resolution_heures'] !== null)
                    {{ $stats['temps_moyen_resolution_heures'] }} h
                @else
                    —
                @endif
            </div>
            <div class="stat-card-hint">Création → date fin (requêtes clôturées)</div>
        </div>
    </div>

    <div class="card card--flush" style="margin-bottom: 1.25rem;">
        <h2 class="card-title" style="padding: 1rem 1.25rem 0;">Performance par technicien</h2>
        <p class="card-sub" style="padding: 0 1.25rem 1rem;">Volume d’interventions terminées (heure de fin dans la période) et délai moyen depuis la création du ticket.</p>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Technicien</th>
                        <th>Volume</th>
                        <th>Délai moyen (h)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['techniciens'] as $ligne)
                        <tr>
                            <td>
                                @if($ligne['technicien'])
                                    {{ $ligne['technicien']->prenom }} {{ $ligne['technicien']->nom }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $ligne['volume'] }}</td>
                            <td>{{ $ligne['delai_moyen_heures'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted" style="padding: 1.5rem 1rem;">Aucune intervention terminée sur cette période.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: grid; gap: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));">
        <div class="card card--flush">
            <h2 class="card-title" style="padding: 1rem 1.25rem 0;">Clients les plus actifs</h2>
            <p class="card-sub" style="padding: 0 1.25rem 1rem;">Nombre de tickets créés sur la période.</p>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['clients_actifs'] as $row)
                            <tr>
                                <td>{{ $row->nom_entreprise }}</td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="muted" style="padding: 1.5rem 1rem;">Aucune donnée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card--flush">
            <h2 class="card-title" style="padding: 1rem 1.25rem 0;">Titres de tickets fréquents</h2>
            <p class="card-sub" style="padding: 0 1.25rem 1rem;">Libellés identiques (pas de catégories dédiées).</p>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Occurrences</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['titres_frequents'] as $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Str::limit($row->titre, 48) }}</td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="muted" style="padding: 1.5rem 1rem;">Aucune donnée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
