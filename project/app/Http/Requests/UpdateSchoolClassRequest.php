<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'school_id' => 'required|integer|exists:schools,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'O nome da turma é obrigatório.',
            'name.max'           => 'O nome da turma deve ter no máximo 255 caracteres.',
            'school_id.required' => 'A escola é obrigatória.',
            'school_id.exists'   => 'A escola selecionada não existe.',
        ];
    }
}
