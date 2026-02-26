<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaCategoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_sala' => $this->id_sala,
            'id_categoria' => $this->id_categoria,
            'sala' => $this->whenLoaded('sala'),
            'categoria' => $this->whenLoaded('categoria'),
            'image_url' => $this->getFirstMediaUrl('images'),
        ];
    }
}
