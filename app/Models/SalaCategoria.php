<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SalaCategoria extends Model implements HasMedia
{
    protected $table = 'sala_categorias';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['id_sala', 'id_categoria'];

    use InteractsWithMedia;

    public function sala()
    {
        return $this->belongsTo(Sala::class, 'id_sala');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }
}