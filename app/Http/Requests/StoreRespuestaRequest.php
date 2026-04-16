<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRespuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_imagen' => ['required', 'exists:imagenes,id'],
            'respuesta' => ['required', 'string', 'max:255'],
            'es_correcta' => ['required', 'boolean'],
            'tiempo' => ['required', 'integer', 'min:0'],
        ];
    }
}
