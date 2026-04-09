@extends('layouts.app')

@section('title', 'Historique des requêtes — ' . config('app.name'))

@section('page_title', 'Historique & traçabilité')
@section('page_subtitle', 'Consultation des tickets selon plusieurs critères. Les données restent en base.')

@section('page_actions')
    <a href="{{ route('requetes.index') }}" class="btn btn-ghost btn-sm">← Liste des requêtes</a>
@endsection

@section('content')
    <form method="get" class="card" style="margin-bottom: 1.25rem;">
        <div class="form-grid" style="align-items: end;">
            @if(auth()->user()->estSuperAdmin() && $clientsFiltre->isNotEmpty())
                <div>
                    <label class="field-label" for="client_id">Client</label>
                    <select class="input" name="client_id" id="client_id">
                        <option value="">Tous</option>
                        @foreach($clientsFiltre as $cl)
                            <option value="{{ $cl->id }}" @selected(request('client_id') == $cl->id)>{{ $cl->nom_entreprise }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($techniciensFiltre->isNotEmpty())
                <div>
                    <label class="field-label" for="technicien_id">Technicien</label>
                    <select class="input" name="technicien_id" id="technicien_id">
                        <option value="">Tous</option>
                        @foreach($techniciensFiltre as $tech)
                            <option value="{{ $tech->id }}" @selected(request('technicien_id') == $tech->id)>{{ $tech->prenom }} {{ $tech->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="grid-column: 1 / -1;">
                <label class="field-label" for="q">Recherche (titre / description)</label>
                <input class="input" type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Mots du problème…" style="max-width: 28rem;">
            </div>

            <div>
                <label class="field-label" for="axe_temporel">Période sur</label>
                <select class="input" name="axe_temporel" id="axe_temporel">
                    <option value="date_creation" @selected(request('axe_temporel', 'date_creation') === 'date_creation')>Date de création (requête)</option>
                    <option value="date_intervention" @selected(request('axe_temporel') === 'date_intervention')>Date d’intervention (requête)</option>
                    <option value="intervention_debut" @selected(request('axe_temporel') === 'intervention_debut')>Heure de début (intervention)</option>
                    <option value="intervention_fin" @selected(request('axe_temporel') === 'intervention_fin')>Heure de fin (intervention)</option>
                </select>
            </div>
            <div>
                <label class="field-label" for="date_debut">Du</label>
                <input class="input" type="date" name="date_debut" id="date_debut" value="{{ request('date_debut') }}">
            </div>
            <div>
                <label class="field-label" for="date_fin">Au</label>
                <input class="input" type="date" name="date_fin" id="date_fin" value="{{ request('date_fin') }}">
            </div>
        </div>
        <div class="inline-forms" style="margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
            <a href="{{ route('historique.requetes') }}" class="btn btn-ghost">Réinitialiser</a>
        </div>
    </form>

    <div class="card card--flush">
        <div class="data-table-wrap">
            <table class="data-table" style="font-size: 0.8125rem;">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Titre</th>
                        <th>Client</th>
                        <th>Technicien</th>
                        <th>Création</th>
                        <th>Interv. (requête)</th>
                        <th>Interv. début / fin</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requetes as $r)
                        <tr>
                            <td style="font-weight: 700;">{{ $r->numeroTicket() }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($r->titre ?? '—', 40) }}</td>
                            <td class="muted">{{ $r->client?->nom_entreprise ?? '—' }}</td>
                            <td>{{ $r->technicien?->prenom }} {{ $r->technicien?->nom ?? '—' }}</td>
                            <td class="muted" style="white-space: nowrap;">{{ $r->date_creation?->format('d/m/Y') ?? '—' }}</td>
                            <td class="muted" style="white-space: nowrap;">{{ $r->date_intervention?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="muted" style="white-space: nowrap; font-size: 0.75rem;">
                                @if($r->intervention)
                                    {{ $r->intervention->heure_debut?->format('d/m H:i') ?? '—' }}
                                    → {{ $r->intervention->heure_fin?->format('d/m H:i') ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="badge badge-muted">{{ $r->statut }}</span></td>
                            <td><a href="{{ route('requetes.show', $r) }}" class="btn btn-ghost btn-sm">Fiche</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="muted" style="text-align: center; padding: 2rem 1rem;">Aucun résultat pour ces critères.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination-nav">{{ $requetes->links() }}</div>
@endsection
