<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePartidaResultadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_sala' => ['nullable', 'exists:salas,id'],
            'id_categoria' => ['nullable', 'exists:categorias,id'],
            'puntuacion' => ['required', 'integer', 'min:0'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $idSala = $this->input('id_sala');
            $idCategoria = $this->input('id_categoria');

            if (empty($idSala) && empty($idCategoria)) {
                $validator->errors()->add('id_sala', 'Debes enviar id_sala o id_categoria.');
            }
        });
    }
}
