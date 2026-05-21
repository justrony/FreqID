<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5 MB em KB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Selecione uma foto para o cadastro biométrico.',
            'photo.image'    => 'O arquivo enviado deve ser uma imagem.',
            'photo.mimes'    => 'Apenas imagens JPG, PNG ou WEBP são aceitas.',
            'photo.max'      => 'A imagem não pode ultrapassar 5 MB.',
        ];
    }
}
