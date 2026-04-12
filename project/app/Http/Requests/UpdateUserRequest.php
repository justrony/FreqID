<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('users', 'name')->ignore($user?->id)->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id)->whereNull('deleted_at'),
            ],
            'reset_password' => ['nullable', 'in:on'],
            'affiliation' => [
                'required',
                'string',
                Rule::in(['seduc', 'school'])
            ],
            'school_ids'   => ['nullable', 'array'],
            'school_ids.*' => ['integer', 'exists:schools,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'name.unique' => 'Este nome já está cadastrado.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'reset_password.in' => 'O valor deve ser "on".',
            'affiliation.required' => 'A afiliação é obrigatória.',
            'affiliation.in' => 'A afiliação deve ser "seduc" ou "school".',
        ];
    }
}
