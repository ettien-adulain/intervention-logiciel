<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planification extends Model
{
    protected $fillable = [
        'requete_id',
        'technicien_id',
        'date_intervention',
        'message',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_intervention' => 'datetime',
        ];
    }

    public function requete()
    {
        return $this->belongsTo(Requetes::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Utilisateurs::class, 'technicien_id');
    }
}
