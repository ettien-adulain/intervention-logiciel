<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interventions extends Model
{
    protected $fillable = [
        'requete_id',
        'technicien_id',
        'rapport',
        'pieces_utilisees',
        'heure_debut',
        'heure_fin',
        'statut'
    ];

    public function requete()
    {
        return $this->belongsTo(Requetes::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Utilisateurs::class, 'technicien_id');
    }

}
