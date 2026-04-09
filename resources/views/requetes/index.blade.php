@extends('layouts.app')

@section('title', 'Requêtes — ' . config('app.name'))

@section('page_title', 'Requêtes d’intervention')
@section('page_subtitle', 'Tickets visibles selon votre périmètre d’accès.')

@section('page_actions')
    @can('create', \App\Models\Requetes::class)
        <a href="{{ route('requetes.create') }}" class="btn btn-primary btn-sm">Nouvelle requête</a>
    @endcan
@endsection

@section('content')
    @if(auth()->user()->estSuperAdmin() && $clientsFiltre->isNotEmpty())
        <form method="get" class="card" style="margin-bottom: 1.25rem;">
            <div class="form-grid">
                <div>
                    <label class="field-label" for="client_id">Filtrer par client</label>
                    <select class="input" name="client_id" id="client_id" style="max-width: 20rem;">
                        <option value="">Tous</option>
                        @foreach($clientsFiltre as $cl)
                            <option value="{{ $cl->id }}" @selected(request('client_id') == $cl->id)>{{ $cl->nom_entreprise }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </div>
        </form>
    @endif

    <div class="card card--flush">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Titre</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th>Médias</th>
                        <th>À traiter</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requetes as $r)
                        <tr>
                            <td class="ticket-num-cell"><span class="ticket-num-display">{{ $r->numeroTicket() }}</span></td>
                            <td>{{ $r->titre ?? '—' }}</td>
                            <td class="muted">{{ $r->client?->nom_entreprise ?? '—' }}</td>
                            <td><span class="badge badge-muted">{{ $r->statut }}</span></td>
                            <td>{{ $r->medias_count }}</td>
                            <td>
                                @if(isset($requetesActionsEnAttenteIds[$r->id]))
                                    <span class="table-pending-badge" title="Une action vous attend sur ce ticket">!</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td><a href="{{ route('requetes.show', $r) }}" class="btn btn-ghost btn-sm">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem 1rem;" class="muted">Aucune requête.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination-nav">{{ $requetes->links() }}</div>
@endsection
