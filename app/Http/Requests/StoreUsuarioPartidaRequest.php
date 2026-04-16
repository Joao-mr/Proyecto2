<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioPartidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_partida' => ['required', 'exists:partidas,id'],
            'puntuacion' => ['required', 'integer', 'min:0'],
        ];
    }
}
