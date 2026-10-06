<?php

namespace App\Http\Requests\Reports;

use App\Enums\RevenueReportGrouping;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HotelRevenueReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'group_by' => ['sometimes', Rule::enum(RevenueReportGrouping::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from.required' => 'A data inicial é obrigatória.',
            'from.date_format' => 'A data inicial deve estar no formato AAAA-MM-DD.',
            'to.required' => 'A data final é obrigatória.',
            'to.date_format' => 'A data final deve estar no formato AAAA-MM-DD.',
            'to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'group_by.enum' => 'O agrupamento deve ser day, month, quarter, semester ou year.',
        ];
    }
}
