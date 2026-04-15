<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartidaImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_partida' => ['required', 'exists:partidas,id'],
            'id_imagen' => ['required', 'exists:imagenes,id'],
            'ronda' => ['required', 'integer', 'min:1'],
        ];
    }
}
