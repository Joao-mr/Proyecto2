<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImagenRequest extends FormRequest
{
    //determina si el usuario esta autorizado para hacer esta solicitud
    public function authorize(): bool
    {
        return true;
    }

    //obtener las reglas de validacion para almacenar una imagen
    public function rules(): array
    {
        return [
            'url' => ['nullable', 'string', 'max:255'],
            'respuesta_correcta' => ['nullable', 'string', 'max:255'],
        ];
    }
}
