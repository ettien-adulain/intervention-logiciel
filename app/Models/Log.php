<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    //
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'date_action'
    ];

    public function user()
    {
        return $this->belongsTo(Utilisateurs::class);
    }
}
