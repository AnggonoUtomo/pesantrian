<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicTermData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAcademicTermApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'ulid', Rule::exists('academic_years', 'id')],
            'code' => [
                'required',
                'string',
                'max:60',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('academic_terms', 'code')->where('academic_year_id', $this->input('academic_year_id')),
            ],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                'max:20',
                Rule::unique('academic_terms', 'sequence')->where('academic_year_id', $this->input('academic_year_id')),
            ],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toData(): UpsertAcademicTermData
    {
        $data = $this->validated();

        return new UpsertAcademicTermData(
            academicYearId: (string) $data['academic_year_id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            sequence: (int) $data['sequence'],
            startsOn: (string) $data['starts_on'],
            endsOn: (string) $data['ends_on'],
            status: (string) $data['status'],
            isActive: (bool) ($data['is_active'] ?? false),
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed'];
    }
}
