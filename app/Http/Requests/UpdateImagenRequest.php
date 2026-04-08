<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImagenRequest extends FormRequest
{
    //determina si el usuario esta autorizado para hacer esta solicitud
    public function authorize(): bool
    {
        return true;
    }

    //obtener las reglas de validacion para actualizar una imagen
    public function rules(): array
    {
        return [
            'url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'respuesta_correcta' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
