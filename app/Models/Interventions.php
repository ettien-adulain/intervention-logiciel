<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interventions extends Model
{
    protected $table = 'interventions';

    protected $fillable = [
        'requete_id',
        'technicien_id',
        'rapport',
        'pieces_utilisees',
        'heure_debut',
        'heure_fin',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'heure_debut' => 'datetime',
            'heure_fin' => 'datetime',
        ];
    }

    public function requete(): BelongsTo
    {
        return $this->belongsTo(Requetes::class);
    }

    public function technicien(): BelongsTo
    {
        return $this->belongsTo(Utilisateurs::class, 'technicien_id');
    }
}
