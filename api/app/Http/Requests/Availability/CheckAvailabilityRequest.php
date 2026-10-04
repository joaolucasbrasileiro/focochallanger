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
        ];
    }
}
