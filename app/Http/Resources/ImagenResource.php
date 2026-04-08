<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImagenResource extends JsonResource
{
    //transforma el recurso a un array para la respuesta JSON
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'respuesta_correcta' => $this->respuesta_correcta,
            'urls' => [
                'original' => $this->getFirstMediaUrl('imagenes'),
                'thumb' => $this->getFirstMediaUrl('imagenes', 'thumb'),
                'preview' => $this->getFirstMediaUrl('imagenes', 'preview'),
            ],
            'has_media' => $this->hasMedia('imagenes'),
            'total_media' => $this->getMedia('imagenes')->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
