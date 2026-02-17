<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaCategoria extends Model
{
    protected $table = 'sala_categorias';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['id_sala', 'id_categoria'];

    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }
}