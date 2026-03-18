<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImagenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
                'sometimes',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'La imagen es requerida.',
            'image.file' => 'Debe ser un archivo válido.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, gif, webp o svg.',
            'image.max' => 'La imagen no debe exceder los 5MB.',
            'respuesta_correcta.boolean' => 'El campo respuesta_correcta debe ser true o false.'
        ];
    }
}