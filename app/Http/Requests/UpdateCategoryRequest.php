<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize()
    {
        // Puedes personalizar la lógica de autorización si es necesario
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . ($this->category->id ?? 'null'),
            'image' => 'nullable|image',
        ];
    }
}
