<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Requetes extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'technicien_id',
        'titre',
        'description',
        'urgence',
        'statut',
        'date_creation',
        'date_planification',
        'date_intervention',
        'date_fin',
    ];

    protected function casts(): array
    {
        return [
            'date_creation' => 'datetime',
            'date_planification' => 'datetime',
            'date_intervention' => 'datetime',
            'date_fin' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(Utilisateurs::class, 'user_id');
    }

    public function technicien()
    {
        return $this->belongsTo(Utilisateurs::class, 'technicien_id');
    }

    public function intervention()
    {
        return $this->hasOne(Interventions::class, 'requete_id');
    }

    public function planifications()
    {
        return $this->hasMany(Planification::class, 'requete_id')->orderByDesc('created_at');
    }

    /** Une ligne d’état par requête (phase 8 — CDC §4.6). */
    public function validation()
    {
        return $this->hasOne(Validations::class, 'requete_id');
    }

    public function medias()
    {
        return $this->hasMany(Medias::class, 'requete_id');
    }

    /** Reçu PDF officiel (phase 10 — au plus une ligne après contrainte unique). */
    public function recu()
    {
        return $this->hasOne(Recus::class, 'requete_id');
    }

    /**
     * Filtre liste requêtes selon le rôle (phase 5 / 6 — périmètre d’accès).
     */
    public function scopeVisiblesPour(Builder $query, Utilisateurs $user): Builder
    {
        if ($user->estSuperAdmin()) {
            return $query;
        }

        if (in_array($user->role, [RoleUtilisateur::ClientAdmin, RoleUtilisateur::ClientUser], true)) {
            return $query->where('client_id', $user->client_id);
        }

        if ($user->role === RoleUtilisateur::Technicien) {
            return $query->where('technicien_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Numéro de ticket type reçu (lisible sur TPE mobile / ticket papier).
     *
     * Format : {CODE}-{AAAAMMJJHHmm}-{id}
     * - CODE : jusqu’à 4 caractères dérivés du nom d’entreprise (sinon C + n° client sur 3 chiffres).
     * - AAAAMMJJHHmm : date et heure de création du ticket (date_creation ou created_at).
     * - id : identifiant unique en base.
     *
     * Exemple : ACME-202604081430-12 → entreprise « ACME… », créé le 08/04/2026 à 14:30, ticket n° 12.
     */
    public function numeroTicket(): string
    {
        $this->loadMissing('client');

        $code = self::codeCourtEntreprisePourTicket($this->client, $this->client_id);
        $dt = $this->date_creation ?? $this->created_at;
        $horodatage = $dt !== null ? $dt->format('YmdHi') : '000000000000';

        return $code.'-'.$horodatage.'-'.$this->id;
    }

    /** Légende lisible du numéro (affichage fiche / PDF). */
    public function numeroTicketLegende(): string
    {
        $this->loadMissing('client');
        $dt = $this->date_creation ?? $this->created_at;
        $quand = $dt !== null ? $dt->format('d/m/Y à H:i') : '—';
        $entreprise = $this->client?->nom_entreprise ?? '—';

        return 'Entreprise : '.$entreprise.' — création : '.$quand.' — réf. interne : '.$this->id;
    }

    private static function codeCourtEntreprisePourTicket(?Client $client, ?int $clientId): string
    {
        if ($client !== null) {
            $ascii = strtoupper(preg_replace(
                '/[^A-Z0-9]/',
                '',
                (string) Str::ascii($client->nom_entreprise ?? '')
            ));
            if (strlen($ascii) >= 2) {
                return substr($ascii, 0, 4);
            }

            return 'C'.str_pad((string) $client->id, 3, '0', STR_PAD_LEFT);
        }

        if ($clientId !== null && $clientId > 0) {
            return 'C'.str_pad((string) $clientId, 3, '0', STR_PAD_LEFT);
        }

        return 'XXXX';
    }
}
