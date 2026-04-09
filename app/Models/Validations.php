<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Validations extends Model
{
    protected $table = 'validations';

    protected $fillable = [
        'requete_id',
    ];

    protected function casts(): array
    {
        return [
            'client_arrivee_at' => 'datetime',
            'client_fin_at' => 'datetime',
            'technicien_fin_at' => 'datetime',
            'client_intervention_en_cours_at' => 'datetime',
        ];
    }

    public function requete(): BelongsTo
    {
        return $this->belongsTo(Requetes::class, 'requete_id');
    }
}
