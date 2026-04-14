<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $salaId = $this->route('sala')?->id ?? $this->sala;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'codigo' => ['sometimes', 'required', 'string', 'max:50', 'unique:salas,codigo,' . $salaId],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['exists:categorias,id'],
        ];
    }
}
