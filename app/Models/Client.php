<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'nom_entreprise',
        'email',
        'telephone',
        'adresse',
        'statut',
    ];

    /**
     * Comptes utilisateurs rattachés à cette entreprise (table `utilisateurs`).
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateurs::class, 'client_id');
    }

    /** @deprecated Préférer `utilisateurs()` ; conservé pour compatibilité. */
    public function users(): HasMany
    {
        return $this->utilisateurs();
    }

    public function requetes(): HasMany
    {
        return $this->hasMany(Requetes::class, 'client_id');
    }

    /** Entreprise éligible aux opérations « normales » (CDC §4.1). */
    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    /**
     * Règle métier phase 3 / lien phase 5 : pas de nouvelle requête si client inactif.
     * À appeler depuis l’action de création de requête (web ou API) lorsqu’elle existera.
     */
    public function peutRecevoirNouvellesRequetes(): bool
    {
        return $this->estActif();
    }
}
