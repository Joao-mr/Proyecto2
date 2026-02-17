<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_sala' => ['required', 'exists:salas,id'],
            'id_categoria' => ['required', 'exists:categorias,id'],
        ];
    }
}