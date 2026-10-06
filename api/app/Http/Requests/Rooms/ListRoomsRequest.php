<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class ListRoomsRequest extends FormRequest
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
            'hotel_id' => ['sometimes', 'integer', 'exists:hotels,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $isActive = $this->input('is_active');

        if (is_string($isActive) && in_array(strtolower($isActive), ['true', 'false'], true)) {
            $this->merge([
                'is_active' => strtolower($isActive) === 'true',
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hotel_id.integer' => 'O hotel deve ser um número inteiro.',
            'hotel_id.exists' => 'O hotel informado não foi encontrado.',
            'name.string' => 'O nome do quarto deve ser um texto.',
            'name.max' => 'O nome do quarto não pode ter mais de :max caracteres.',
            'is_active.boolean' => 'O filtro de status deve ser verdadeiro ou falso.',
            'per_page.integer' => 'A quantidade por página deve ser um número inteiro.',
            'per_page.min' => 'A quantidade por página deve ser pelo menos :min.',
            'per_page.max' => 'A quantidade por página não pode ser maior que :max.',
        ];
    }
}
