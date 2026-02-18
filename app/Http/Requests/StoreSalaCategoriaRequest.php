<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalaCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_sala' => ['required', 'exists:salas,id'],
            'id_categoria' => [
                'required',
                'exists:categorias,id',
                Rule::unique('sala_categorias')->where(function ($query) {
                    return $query
                        ->where('id_sala', $this->input('id_sala'))
                        ->where('id_categoria', $this->input('id_categoria'));
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_sala.required' => 'El campo id_sala es obligatorio.',
            'id_sala.exists' => 'La sala seleccionada no existe.',
            'id_categoria.required' => 'El campo id_categoria es obligatorio.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
            'id_categoria.unique' => 'La relación sala-categoría ya existe.',
        ];
    }
}