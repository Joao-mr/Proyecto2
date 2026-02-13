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
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:50', 'unique:salas,codigo'],
            'id_creador' => ['required', 'exists:users,id'],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['exists:categorias,id'],
        ];
    }
}
