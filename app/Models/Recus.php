<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recus extends Model
{
    protected $table = 'recus';

    protected $fillable = [
        'requete_id',
        'chemin_pdf'
    ];

    public function requete()
    {
        return $this->belongsTo(Requetes::class);
    }
}
