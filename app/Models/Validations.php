<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validations extends Model
{
    //
    protected $fillable = [
        'requete_id',
        'user_id',
        'type',
        'statut',
        'date_validation'
    ];

    public function requete()
    {
        return $this->belongsTo(Requetes::class);
    }

    public function user()
    {
        return $this->belongsTo(Utilisateurs::class);
    }
}
