<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medias extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'requete_id',
        'type',
        'chemin',
        'taille',
    ];

    public function requete()
    {
        return $this->belongsTo(Requetes::class, 'requete_id');
    }
}
