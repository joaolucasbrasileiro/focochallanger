<?php

namespace App\Http\Requests\Imports;

use App\Models\ImportIssue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListImportIssuesRequest extends FormRequest
{
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
            'import_run_id' => ['sometimes', 'integer', 'exists:import_runs,id'],
            'source' => ['sometimes', 'string', Rule::in([
                ImportIssue::SourceHotel,
                ImportIssue::SourceRoom,
                ImportIssue::SourceReservation,
            ])],
            'status' => ['sometimes', 'string', Rule::in([
                ImportIssue::StatusIncomplete,
                ImportIssue::StatusResolved,
                ImportIssue::StatusIgnored,
            ])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
