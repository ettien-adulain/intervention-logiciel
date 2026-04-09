<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $requete->numeroTicket() }}</title>
    <style>
        @page { margin: 6mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5pt;
            color: #111;
            margin: 0;
            padding: 0 2mm;
            line-height: 1.35;
            max-width: 72mm;
            margin-left: auto;
            margin-right: auto;
        }
        .rule {
            border: none;
            border-top: 1px dashed #333;
            margin: 0.5rem 0;
        }
        .center { text-align: center; }
        .big {
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 0.02em;
            word-break: break-all;
        }
        .title { font-size: 10pt; font-weight: 700; margin: 0 0 0.25rem; }
        .muted { color: #444; font-size: 7.5pt; }
        .row { margin: 0.2rem 0; }
        .label { font-weight: 700; display: inline; }
        ul { margin: 0.25rem 0; padding-left: 1rem; }
        li { margin: 0.1rem 0; }
        .rapport { white-space: pre-wrap; margin: 0.25rem 0 0; font-size: 7.5pt; }
    </style>
</head>
<body>
    <div class="center">
        <p class="title">REÇU D’INTERVENTION</p>
        <p class="muted">Édité le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <hr class="rule">

    <p class="center big">{{ $requete->numeroTicket() }}</p>
    <p class="center muted" style="margin-top: 0.15rem;">{{ $requete->numeroTicketLegende() }}</p>

    <hr class="rule">

    <p class="row"><span class="label">Ticket</span> — {{ $requete->titre ?? 'Sans titre' }}</p>
    <p class="row"><span class="label">Statut</span> — {{ $requete->statut }} · <span class="label">Urgence</span> — {{ $requete->urgence }}</p>
    <p class="row"><span class="label">Créé</span> — {{ $requete->date_creation?->format('d/m/Y H:i') ?? '—' }}</p>
    @if($requete->date_fin)
        <p class="row"><span class="label">Fin</span> — {{ $requete->date_fin->format('d/m/Y H:i') }}</p>
    @endif

    <hr class="rule">

    <p class="row"><span class="label">Client</span></p>
    <p class="row">{{ $requete->client?->nom_entreprise ?? '—' }}</p>
    <p class="row muted">Contact : {{ $requete->user?->prenom }} {{ $requete->user?->nom }}</p>

    <hr class="rule">

    <p class="row"><span class="label">Technicien</span> — {{ $requete->technicien?->prenom }} {{ $requete->technicien?->nom ?? '—' }}</p>

    <hr class="rule">

    <p class="row"><span class="label">Intervention</span></p>
    @if($requete->intervention)
        <p class="row">{{ str_replace('_', ' ', $requete->intervention->statut) }}</p>
        <p class="row muted">Début : {{ $requete->intervention->heure_debut?->format('d/m/Y H:i') ?? '—' }} · Fin : {{ $requete->intervention->heure_fin?->format('d/m/Y H:i') ?? '—' }}</p>
        @if($requete->intervention->rapport)
            <p class="label">Rapport</p>
            <p class="rapport">{{ $requete->intervention->rapport }}</p>
        @endif
        @if($requete->intervention->pieces_utilisees)
            <p class="label" style="margin-top: 0.35rem;">Pièces</p>
            <p class="rapport">{{ $requete->intervention->pieces_utilisees }}</p>
        @endif
    @else
        <p class="row muted">—</p>
    @endif

    <hr class="rule">

    <p class="label">Validations</p>
    @php($val = $requete->validation)
    <ul class="muted">
        <li>Arrivée (client) : {{ $val?->client_arrivee_at?->format('d/m/Y H:i') ?? '—' }}</li>
        <li>En cours (client) : {{ $val?->client_intervention_en_cours_at?->format('d/m/Y H:i') ?? '—' }}</li>
        <li>Fin (client) : {{ $val?->client_fin_at?->format('d/m/Y H:i') ?? '—' }}</li>
        <li>Fin (technicien) : {{ $val?->technicien_fin_at?->format('d/m/Y H:i') ?? '—' }}</li>
    </ul>

    @if($requete->description)
        <hr class="rule">
        <p class="label">Description initiale</p>
        <p class="rapport">{{ $requete->description }}</p>
    @endif

    <hr class="rule">
    <p class="center muted" style="margin-bottom: 0;">Document non contractuel — conservation recommandée</p>
</body>
</html>
