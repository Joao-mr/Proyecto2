<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'           => ['required', 'string', 'max:255'],
            'codigo'           => ['required', 'string', 'max:50', 'unique:salas,codigo'],
            'tiempo_respuesta' => ['nullable', 'integer', 'min:5', 'max:300'],
            'categorias'       => ['nullable', 'array'],
            'categorias.*'     => ['integer', 'exists:categorias,id'],
        ];
    }
}
