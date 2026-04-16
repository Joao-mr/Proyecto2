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

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_partida', 'id_partida', 'id_usuario')->withPivot('puntuacion');
    }

    public function imagenes()
    {
        return $this->belongsToMany(Imagen::class, 'partida_imagen', 'id_partida', 'id_imagen')->withPivot('ronda');
    }
}
