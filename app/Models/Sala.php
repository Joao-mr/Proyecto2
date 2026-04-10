<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    protected $fillable = ['nombre', 'codigo', 'id_creador', 'tiempo_respuesta'];

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'sala_categorias', 'id_sala', 'id_categoria');
    }

    public function partidas()
    {
        return $this->hasMany(Partida::class, 'id_sala');
    }
}
