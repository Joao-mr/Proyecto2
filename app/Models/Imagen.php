<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Imagen extends Model implements HasMedia
{
    use InteractsWithMedia; //permite usar media library

    protected $table = 'imagenes';

    //que datos guardara la tabla imagenes
    protected $fillable = [
        'url',
        'respuesta_correcta',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    //se crea una carpeta "logica" que se llama imagenes
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('imagenes')
        //archivos permitidos
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml'
            ])
            ->singleFile(); //solo una imagen por registro
    }

    //crea versiones automaticas de la imagen
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('imagenes');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->performOnCollections('imagenes');
    }
}
