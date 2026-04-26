<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRespuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_imagen' => ['sometimes', 'required', 'exists:imagenes,id'],
            'respuesta' => ['sometimes', 'required', 'string', 'max:255'],
            'es_correcta' => ['sometimes', 'required', 'boolean'],
            'tiempo' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
