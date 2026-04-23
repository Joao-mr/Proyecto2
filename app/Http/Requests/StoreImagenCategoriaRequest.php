<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImagenCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_imagen' => ['required', 'exists:imagenes,id'],
            'id_categoria' => ['required', 'exists:categorias,id'],
        ];
    }
}
