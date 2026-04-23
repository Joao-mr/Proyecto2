<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    //obtener las reglas de validacion para subir una imagen
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,gif,webp,svg',
                'max:5120' // 5MB max
            ],
            'respuesta_correcta' => [
                'nullable',
                'string',
                'max:255'
            ]
        ];
    }

    //obtener los mensajes de error personalizados para la validacion
    public function messages(): array
    {
        return [
            'image.required' => 'La imagen es requerida.',
            'image.file' => 'Debe ser un archivo válido.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, gif, webp o svg.',
            'image.max' => 'La imagen no debe exceder los 5MB.',
            'respuesta_correcta.string' => 'La respuesta correcta debe ser un texto.',
            'respuesta_correcta.max' => 'La respuesta correcta no debe exceder 255 caracteres.'
        ];
    }
}

