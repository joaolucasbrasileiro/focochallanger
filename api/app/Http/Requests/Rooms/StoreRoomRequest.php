<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hotel_id.required' => 'O hotel é obrigatório.',
            'hotel_id.integer' => 'O hotel informado é inválido.',
            'hotel_id.exists' => 'O hotel informado não foi encontrado.',
            'name.required' => 'O nome do quarto é obrigatório.',
            'name.string' => 'O nome do quarto deve ser um texto.',
            'name.max' => 'O nome do quarto não pode ter mais de :max caracteres.',
            'is_active.boolean' => 'O campo de status do quarto deve ser verdadeiro ou falso.',
        ];
    }
}
