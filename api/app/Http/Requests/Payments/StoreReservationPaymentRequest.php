<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationPaymentRequest extends FormRequest
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
            'method_code' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'decimal:0,2', 'min:0.01'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $methodCode = $this->input('method_code');

        if (is_string($methodCode)) {
            $this->merge([
                'method_code' => trim($methodCode),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'method_code.required' => 'O método de pagamento é obrigatório.',
            'method_code.string' => 'O método de pagamento deve ser um texto.',
            'method_code.max' => 'O método de pagamento não pode ter mais de :max caracteres.',
            'amount.required' => 'O valor do pagamento é obrigatório.',
            'amount.decimal' => 'O valor do pagamento deve possuir no máximo duas casas decimais.',
            'amount.min' => 'O valor do pagamento deve ser maior que zero.',
        ];
    }
}
