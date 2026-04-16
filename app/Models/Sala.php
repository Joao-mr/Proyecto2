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

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_sala', 'id_sala', 'id_usuario')->withPivot('fecha_entrada');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'id_creador');
    }
}
