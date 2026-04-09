@extends('layouts.app')

@section('title', $requete->numeroTicket() . ' — ' . config('app.name'))

@section('page_title', $requete->numeroTicket())
@section('page_subtitle', $requete->titre ?? 'Sans titre')

@section('page_actions')
    <a href="{{ route('requetes.index') }}" class="btn btn-ghost btn-sm">← Liste des requêtes</a>
@endsection

@section('content')
    <div class="tabs-radios">
        <input type="radio" name="req-tabs" id="req-tab-summary" class="tab-input" checked>
        <input type="radio" name="req-tabs" id="req-tab-ops" class="tab-input">
        <input type="radio" name="req-tabs" id="req-tab-files" class="tab-input">

        <div class="tab-bar" role="tablist" aria-label="Sections de la fiche requête">
            <label for="req-tab-summary" role="tab">Résumé</label>
            <label for="req-tab-ops" role="tab" @class(['tab-pending' => !empty($requeteFicheOpsAlerte ?? false)])>
                Planification &amp; intervention
                @if(!empty($requeteFicheOpsAlerte ?? false))
                    <span class="tab-pending-dot" title="Action à traiter dans cet onglet" aria-hidden="true"></span>
                @endif
            </label>
            <label for="req-tab-files" role="tab">Documents</label>
        </div>

        <div class="tab-panel panel-summary">
            <div class="card">
                <h2 class="card-title">Détails du ticket</h2>
                <p class="text-sm muted ticket-num-legend" style="margin: 0 0 0.85rem;">{{ $requete->numeroTicketLegende() }}</p>
                <p class="text-sm muted" style="margin: 0 0 0.85rem;">N° affiché : <strong class="ticket-num-display">{{ $requete->numeroTicket() }}</strong> — format reçu : code entreprise, date-heure de création (AAAAMMJJHHmm), identifiant unique.</p>
                <dl style="margin: 0; font-size: 0.875rem;">
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Urgence :</strong> {{ $requete->urgence }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Statut :</strong> <span class="badge badge-muted">{{ $requete->statut }}</span></div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Client :</strong> {{ $requete->client?->nom_entreprise ?? '—' }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Auteur :</strong> {{ $requete->user?->prenom }} {{ $requete->user?->nom }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Technicien assigné :</strong> {{ $requete->technicien?->prenom }} {{ $requete->technicien?->nom ?? '—' }}</div>
                    @if($requete->date_planification)
                        <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Date planifiée (requête) :</strong> {{ $requete->date_planification->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($requete->description)
                        <div style="margin-top: 0.75rem; white-space: pre-wrap;">{{ $requete->description }}</div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="tab-panel panel-ops">
            {{-- Planification --}}
            <div class="card" style="margin-bottom: 1.25rem;">
                <h2 class="card-title">Planification</h2>

                @can('assignerTechnicien', $requete)
                    @if($techniciensPourPlanif->isEmpty())
                        <p class="text-sm muted">Aucun technicien actif : ajoutez un compte rôle « technicien » pour planifier.</p>
                    @else
                        <form method="post" action="{{ route('requetes.planifications.store', $requete) }}" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--app-border);">
                            @csrf
                            <p class="field-hint">Nouvelle planification : visible immédiatement dans l’application pour le client et le technicien assigné.</p>
                            <div class="form-field">
                                <label class="field-label" for="technicien_id">Technicien</label>
                                <select class="input" name="technicien_id" id="technicien_id" required style="max-width: 24rem;">
                                    @foreach($techniciensPourPlanif as $tech)
                                        <option value="{{ $tech->id }}" @selected((int) $requete->technicien_id === (int) $tech->id)>{{ $tech->prenom }} {{ $tech->nom }} — {{ $tech->email }}</option>
                                    @endforeach
                                </select>
                                @error('technicien_id')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="date_intervention">Date et heure d’intervention</label>
                                <input class="input" type="datetime-local" name="date_intervention" id="date_intervention" required value="{{ old('date_intervention') }}" style="max-width: 24rem;">
                                @error('date_intervention')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="message_planif">Message (optionnel)</label>
                                <textarea class="input" name="message" id="message_planif" rows="3" style="max-width: 36rem;">{{ old('message') }}</textarea>
                                @error('message')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer la planification</button>
                        </form>
                    @endif
                @endcan

                <h3 class="text-sm muted" style="margin: 0 0 0.75rem; font-weight: 600;">Historique</h3>
                <ul class="stack-list">
                    @forelse ($requete->planifications as $p)
                        <li>
                            <div><strong>{{ $p->date_intervention->format('d/m/Y H:i') }}</strong> — {{ $p->technicien?->prenom }} {{ $p->technicien?->nom }} — <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $p->statut) }}</span></div>
                            @if($p->message)
                                <div style="margin-top: 0.35rem; white-space: pre-wrap; color: #475569;">{{ $p->message }}</div>
                            @endif
                            <div class="inline-forms" style="margin-top: 0.5rem;">
                                @can('confirmerPlanification', $requete)
                                    @if($p->statut === 'planifiee')
                                        <form method="post" action="{{ route('requetes.planifications.update', [$requete, $p]) }}" onsubmit="return confirm('Confirmer cette planification ?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="statut" value="confirmee">
                                            <button type="submit" class="btn btn-primary btn-sm">Confirmer (client)</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('assignerTechnicien', $requete)
                                    @if(in_array($p->statut, ['planifiee', 'confirmee'], true))
                                        <form method="post" action="{{ route('requetes.planifications.update', [$requete, $p]) }}" onsubmit="return confirm('Annuler cette planification ?');">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="statut" value="annulee">
                                            <button type="submit" class="btn btn-ghost btn-sm btn-danger-ghost">Annuler</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="muted text-sm">Aucune planification enregistrée.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Validations --}}
            @php
                $v = $requete->validation;
            @endphp
            <div class="card" style="margin-bottom: 1.25rem;">
                <h2 class="card-title">Validations</h2>
                <p class="card-sub">
                    Horodatages des confirmations client et technicien. Le technicien doit être assigné pour les validations client.
                </p>
                <dl style="margin: 0 0 1rem; font-size: 0.875rem;">
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Arrivée du technicien (client) :</strong>
                        {{ $v?->client_arrivee_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Intervention en cours (client) :</strong>
                        {{ $v?->client_intervention_en_cours_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Fin d’intervention (client) :</strong>
                        {{ $v?->client_fin_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Fin d’intervention (technicien) :</strong>
                        {{ $v?->technicien_fin_at?->format('d/m/Y H:i') ?? '—' }}</div>
                </dl>

                <div class="inline-forms">
                    @can('validerArriveeClient', $requete)
                        @if(!$v?->client_arrivee_at)
                            <form method="post" action="{{ route('requetes.validations.store', $requete) }}">
                                @csrf
                                <input type="hidden" name="etape" value="client_arrivee">
                                <button type="submit" class="btn btn-primary btn-sm">Valider l’arrivée du technicien</button>
                            </form>
                        @endif
                    @endcan
                    @can('validerInterventionEnCoursClient', $requete)
                        @if(!$v?->client_intervention_en_cours_at)
                            <form method="post" action="{{ route('requetes.validations.store', $requete) }}">
                                @csrf
                                <input type="hidden" name="etape" value="client_intervention_en_cours">
                                <button type="submit" class="btn btn-primary btn-sm">Confirmer l’intervention en cours</button>
                            </form>
                        @endif
                    @endcan
                    @can('validerFinInterventionClient', $requete)
                        @if(!$v?->client_fin_at)
                            <form method="post" action="{{ route('requetes.validations.store', $requete) }}">
                                @csrf
                                <input type="hidden" name="etape" value="client_fin">
                                <button type="submit" class="btn btn-primary btn-sm">Valider la fin d’intervention (client)</button>
                            </form>
                        @endif
                    @endcan
                    @can('validerFinTechnicien', $requete)
                        @if(!$v?->technicien_fin_at)
                            <form method="post" action="{{ route('requetes.validations.store', $requete) }}">
                                @csrf
                                <input type="hidden" name="etape" value="technicien_fin">
                                <button type="submit" class="btn btn-primary btn-sm">Confirmer la fin (technicien)</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Intervention --}}
            @php
                $intervention = $requete->intervention;
            @endphp
            <div class="card">
                <h2 class="card-title">Intervention sur le terrain</h2>
                <p class="card-sub">
                    Compte rendu (rapport, pièces, horaires, statut). Réservé au technicien assigné ; le statut de la requête passe en <strong>en_cours</strong> puis <strong>terminée</strong> selon l’intervention.
                </p>

                @if($intervention)
                    <dl style="margin: 0 0 1rem; font-size: 0.875rem;">
                        <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Statut :</strong> {{ str_replace('_', ' ', $intervention->statut) }}</div>
                        <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Début :</strong> {{ $intervention->heure_debut?->format('d/m/Y H:i') ?? '—' }}</div>
                        <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Fin :</strong> {{ $intervention->heure_fin?->format('d/m/Y H:i') ?? '—' }}</div>
                        <div class="form-field" style="margin-bottom: 0.35rem;"><strong>Technicien :</strong> {{ $intervention->technicien?->prenom }} {{ $intervention->technicien?->nom }}</div>
                    </dl>
                    @if($intervention->rapport)
                        <div class="form-field text-sm">
                            <strong>Rapport</strong>
                            <div style="margin-top: 0.35rem; white-space: pre-wrap;">{{ $intervention->rapport }}</div>
                        </div>
                    @endif
                    @if($intervention->pieces_utilisees)
                        <div class="form-field text-sm">
                            <strong>Pièces utilisées</strong>
                            <div style="margin-top: 0.35rem; white-space: pre-wrap;">{{ $intervention->pieces_utilisees }}</div>
                        </div>
                    @endif
                @else
                    <p class="text-sm muted" style="margin: 0 0 1rem;">Aucune intervention enregistrée.</p>
                @endif

                @can('gererInterventionTerrain', $requete)
                    @if($intervention)
                        <form method="post" action="{{ route('requetes.intervention.update', $requete) }}" style="max-width: 36rem;">
                            @csrf
                            @method('PATCH')
                            <div class="form-field">
                                <label class="field-label" for="int_rapport">Rapport (actions effectuées)</label>
                                <textarea class="input" name="rapport" id="int_rapport" rows="4">{{ old('rapport', $intervention->rapport) }}</textarea>
                                @error('rapport')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_pieces">Pièces utilisées</label>
                                <textarea class="input" name="pieces_utilisees" id="int_pieces" rows="2">{{ old('pieces_utilisees', $intervention->pieces_utilisees) }}</textarea>
                                @error('pieces_utilisees')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_debut">Heure de début</label>
                                <input class="input" type="datetime-local" name="heure_debut" id="int_debut" value="{{ old('heure_debut', $intervention->heure_debut?->format('Y-m-d\TH:i')) }}" style="max-width: 24rem;">
                                @error('heure_debut')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_fin">Heure de fin</label>
                                <input class="input" type="datetime-local" name="heure_fin" id="int_fin" value="{{ old('heure_fin', $intervention->heure_fin?->format('Y-m-d\TH:i')) }}" style="max-width: 24rem;">
                                @error('heure_fin')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <span class="field-label">Statut</span>
                                @if($intervention->statut === 'terminee')
                                    <input type="hidden" name="statut" value="terminee">
                                    <p class="text-sm" style="margin: 0;">Terminée (le statut ne peut plus être repassé en cours.)</p>
                                @else
                                    <select class="input" name="statut" id="int_statut" required style="max-width: 24rem;">
                                        <option value="en_cours" @selected(old('statut', $intervention->statut) === 'en_cours')>En cours</option>
                                        <option value="terminee" @selected(old('statut', $intervention->statut) === 'terminee')>Terminée</option>
                                    </select>
                                @endif
                                @error('statut')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('requetes.intervention.store', $requete) }}" style="max-width: 36rem;">
                            @csrf
                            <div class="form-field">
                                <label class="field-label" for="int_rapport_n">Rapport (actions effectuées)</label>
                                <textarea class="input" name="rapport" id="int_rapport_n" rows="4">{{ old('rapport') }}</textarea>
                                @error('rapport')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_pieces_n">Pièces utilisées</label>
                                <textarea class="input" name="pieces_utilisees" id="int_pieces_n" rows="2">{{ old('pieces_utilisees') }}</textarea>
                                @error('pieces_utilisees')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_debut_n">Heure de début</label>
                                <input class="input" type="datetime-local" name="heure_debut" id="int_debut_n" value="{{ old('heure_debut') }}" style="max-width: 24rem;">
                                @error('heure_debut')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_fin_n">Heure de fin</label>
                                <input class="input" type="datetime-local" name="heure_fin" id="int_fin_n" value="{{ old('heure_fin') }}" style="max-width: 24rem;">
                                @error('heure_fin')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="form-field">
                                <label class="field-label" for="int_statut_n">Statut</label>
                                <select class="input" name="statut" id="int_statut_n" required style="max-width: 24rem;">
                                    <option value="en_cours" @selected(old('statut') === 'en_cours' || old('statut') === null)>En cours</option>
                                    <option value="terminee" @selected(old('statut') === 'terminee')>Terminée</option>
                                </select>
                                @error('statut')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Créer l’intervention</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>

        <div class="tab-panel panel-files">
            <div class="card" style="margin-bottom: 1.25rem;">
                <h2 class="card-title">Reçu PDF</h2>
                <p class="card-sub">
                    Document d’archive après intervention <strong>terminée</strong>. Génération et téléchargement dans l’application pour les profils autorisés.
                </p>
                <div class="inline-forms">
                    @can('genererRecuPdf', $requete)
                        <form method="post" action="{{ route('requetes.recu.pdf.store', $requete) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">{{ $requete->recu ? 'Régénérer le PDF' : 'Générer le PDF' }}</button>
                        </form>
                    @endcan
                    @can('telechargerRecuPdf', $requete)
                        <a href="{{ route('requetes.recu.pdf.download', $requete) }}" class="btn btn-ghost btn-sm" download>Télécharger le PDF</a>
                    @endcan
                </div>
            </div>

            <div class="card">
                <h2 class="card-title">Pièces jointes</h2>

                @can('update', $requete)
                    <form method="post" action="{{ route('requetes.medias.store', $requete) }}" enctype="multipart/form-data" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--app-border);">
                        @csrf
                        <label class="field-label" for="fichier">Ajouter un fichier</label>
                        <p class="field-hint">
                            Images (JPEG, PNG, GIF, WebP) ou vidéos (MP4, MOV, WebM). Taille max : {{ config('medias.max_upload_ko') }} Ko.
                        </p>
                        <input type="file" id="fichier" name="fichier" required accept="image/*,video/mp4,video/quicktime,video/webm">
                        @error('fichier')<p class="form-error">{{ $message }}</p>@enderror
                        <div style="margin-top: 0.75rem;">
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </form>
                @endcan

                <ul class="stack-list">
                    @forelse ($requete->medias as $m)
                        <li class="media-row">
                            <div style="flex: 1;">
                                @if($m->type === 'image')
                                    <a href="{{ route('requetes.medias.fichier', [$requete, $m]) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('requetes.medias.fichier', [$requete, $m]) }}" alt="">
                                    </a>
                                @else
                                    <video controls src="{{ route('requetes.medias.fichier', [$requete, $m]) }}"></video>
                                @endif
                                <div class="text-sm muted" style="margin-top: 0.35rem;">
                                    {{ strtoupper($m->type) }} · {{ number_format($m->taille / 1024, 1, ',', ' ') }} Ko
                                    · <a href="{{ route('requetes.medias.fichier', [$requete, $m]) }}" target="_blank" rel="noopener">Ouvrir</a>
                                </div>
                            </div>
                            @can('update', $requete)
                                <form action="{{ route('requetes.medias.destroy', [$requete, $m]) }}" method="post" onsubmit="return confirm('Supprimer ce fichier ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm btn-danger-ghost">Supprimer</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="muted text-sm">Aucune pièce jointe.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
