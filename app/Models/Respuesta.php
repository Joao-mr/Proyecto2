<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $fillable = [
        'id_usuario',
        'id_imagen',
        'respuesta',
        'es_correcta',
        'tiempo',
    ];

    protected $casts = [
        'es_correcta' => 'boolean',
        'tiempo' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function imagen()
    {
        return $this->belongsTo(Imagen::class, 'id_imagen');
    }
}
