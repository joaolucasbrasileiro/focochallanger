<?php

namespace App\Http\Requests\Hotels;

use Illuminate\Foundation\Http\FormRequest;

class ListHotelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'external_id' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'O nome do hotel deve ser um texto.',
            'name.max' => 'O nome do hotel não pode ter mais de :max caracteres.',
            'external_id.integer' => 'O identificador externo deve ser um número inteiro.',
            'per_page.integer' => 'A quantidade por página deve ser um número inteiro.',
            'per_page.min' => 'A quantidade por página deve ser pelo menos :min.',
            'per_page.max' => 'A quantidade por página não pode ser maior que :max.',
        ];
    }
}
