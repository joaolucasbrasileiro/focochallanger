<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hotel_id.integer' => 'O hotel informado é inválido.',
            'hotel_id.exists' => 'O hotel informado não foi encontrado.',
            'name.string' => 'O nome do quarto deve ser um texto.',
            'name.max' => 'O nome do quarto não pode ter mais de :max caracteres.',
            'capacity.integer' => 'A capacidade do quarto deve ser um número inteiro.',
            'capacity.min' => 'A capacidade do quarto deve ser de pelo menos :min.',
            'capacity.max' => 'A capacidade do quarto não pode ser maior que :max.',
            'is_active.boolean' => 'O campo de status do quarto deve ser verdadeiro ou falso.',
        ];
    }
}
