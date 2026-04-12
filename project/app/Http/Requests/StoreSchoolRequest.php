<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'inep_code' => 'required|string|max:8|unique:schools,inep_code',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'O nome da escola é obrigatório.',
            'name.max'           => 'O nome da escola deve ter no máximo 255 caracteres.',
            'inep_code.required' => 'O código INEP é obrigatório.',
            'inep_code.max'      => 'O código INEP deve ter no máximo 8 caracteres.',
            'inep_code.unique'   => 'O código INEP já está cadastrado.',
        ];
    }
}
