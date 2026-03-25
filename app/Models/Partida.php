<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id_sala',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala');
    }
}
