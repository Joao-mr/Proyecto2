<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion'];

    //
    public function salas()
    {
        return $this->belongsToMany(Sala::class, 'sala_categorias', 'id_categoria', 'id_sala');
    }

    public function imagenes()
    {
        return $this->belongsToMany(Imagen::class, 'imagen_categoria', 'id_categoria', 'id_imagen');
    }
}
