<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'registration' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:students,cpf',
            'nome_mae' => 'required|string|max:255',
            'nome_pai' => 'nullable|string|max:255',
            'data_nascimento' => 'required|date',
        ];
    }
}
