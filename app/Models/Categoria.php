<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre'];

    //
    public function salas()
    {
        return $this->belongsToMany(Sala::class, 'sala_categorias', 'id_categoria', 'id_sala');
    }
}
