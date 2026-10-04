<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
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
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_in.required' => 'A data de check-in é obrigatória.',
            'check_in.date_format' => 'A data de check-in deve estar no formato AAAA-MM-DD.',
            'check_out.required' => 'A data de check-out é obrigatória.',
            'check_out.date_format' => 'A data de check-out deve estar no formato AAAA-MM-DD.',
            'check_out.after' => 'A data de check-out deve ser posterior à data de check-in.',
            'guests.required' => 'A quantidade de hóspedes é obrigatória.',
            'guests.integer' => 'A quantidade de hóspedes deve ser um número inteiro.',
            'guests.min' => 'A quantidade de hóspedes deve ser de pelo menos :min.',
            'guests.max' => 'A quantidade de hóspedes não pode ser maior que :max.',
        ];
    }
}
