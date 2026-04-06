<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requetes extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
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
        return $this->hasOne(Interventions::class);
    }

    public function planification()
    {
        return $this->hasOne(Planification::class);
    }

    public function validations()
    {
        return $this->hasMany(Validations::class);
    }

    public function medias()
    {
        return $this->hasMany(Medias::class);
    }

}
