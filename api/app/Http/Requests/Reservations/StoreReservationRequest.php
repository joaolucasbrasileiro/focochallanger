<?php

namespace App\Http\Requests\Reservations;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
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
            'room_name' => ['required', 'string', 'max:255'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.first_name' => ['required', 'string', 'max:255'],
            'guests.*.last_name' => ['required', 'string', 'max:255'],
            'guests.*.phone' => ['required', 'string', 'max:32'],
            'dailies' => ['required', 'array', 'min:1'],
            'dailies.*.daily_date' => ['required', 'date_format:Y-m-d'],
            'dailies.*.amount' => ['required', 'decimal:0,2', 'min:0.01'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $checkIn = CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->input('check_in'));
                $checkOut = CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->input('check_out'));
                $expectedDailyDates = [];

                for ($date = $checkIn; $date->lt($checkOut); $date = $date->addDay()) {
                    $expectedDailyDates[] = $date->toDateString();
                }

                $dailyDates = array_column($this->input('dailies'), 'daily_date');

                if (count($dailyDates) !== count(array_unique($dailyDates))) {
                    $validator->errors()->add('dailies', 'Cada diária deve possuir uma data única.');

                    return;
                }

                sort($dailyDates);

                if ($dailyDates !== $expectedDailyDates) {
                    $validator->errors()->add('dailies', 'As diárias devem cobrir todos os dias entre o check-in e o check-out.');
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $roomName = $this->input('room_name');

        if (is_string($roomName)) {
            $this->merge([
                'room_name' => trim($roomName),
            ]);
        }
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
            'room_name.required' => 'O nome da acomodação é obrigatório.',
            'room_name.string' => 'O nome da acomodação deve ser um texto.',
            'room_name.max' => 'O nome da acomodação não pode ter mais de :max caracteres.',
            'check_in.required' => 'A data de check-in é obrigatória.',
            'check_in.date_format' => 'A data de check-in deve estar no formato AAAA-MM-DD.',
            'check_out.required' => 'A data de check-out é obrigatória.',
            'check_out.date_format' => 'A data de check-out deve estar no formato AAAA-MM-DD.',
            'check_out.after' => 'A data de check-out deve ser posterior à data de check-in.',
            'guests.required' => 'É necessário informar pelo menos um hóspede.',
            'guests.array' => 'Os hóspedes devem ser enviados em uma lista.',
            'guests.min' => 'É necessário informar pelo menos um hóspede.',
            'guests.*.first_name.required' => 'O nome do hóspede é obrigatório.',
            'guests.*.first_name.string' => 'O nome do hóspede deve ser um texto.',
            'guests.*.first_name.max' => 'O nome do hóspede não pode ter mais de :max caracteres.',
            'guests.*.last_name.required' => 'O sobrenome do hóspede é obrigatório.',
            'guests.*.last_name.string' => 'O sobrenome do hóspede deve ser um texto.',
            'guests.*.last_name.max' => 'O sobrenome do hóspede não pode ter mais de :max caracteres.',
            'guests.*.phone.required' => 'O telefone do hóspede é obrigatório.',
            'guests.*.phone.string' => 'O telefone do hóspede deve ser um texto.',
            'guests.*.phone.max' => 'O telefone do hóspede não pode ter mais de :max caracteres.',
            'dailies.required' => 'É necessário informar as diárias da reserva.',
            'dailies.array' => 'As diárias devem ser enviadas em uma lista.',
            'dailies.min' => 'É necessário informar pelo menos uma diária.',
            'dailies.*.daily_date.required' => 'A data da diária é obrigatória.',
            'dailies.*.daily_date.date_format' => 'A data da diária deve estar no formato AAAA-MM-DD.',
            'dailies.*.amount.required' => 'O valor da diária é obrigatório.',
            'dailies.*.amount.decimal' => 'O valor da diária deve possuir no máximo duas casas decimais.',
            'dailies.*.amount.min' => 'O valor da diária deve ser maior que zero.',
        ];
    }
}
