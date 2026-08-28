<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateAcademicTermApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $termId = (string) $this->route('term');
        $academicYearId = $this->input('academic_year_id');

        return [
            'academic_year_id' => ['sometimes', 'required', 'ulid', Rule::exists('academic_years', 'id')],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:60',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('academic_terms', 'code')->where('academic_year_id', $academicYearId)->ignore($termId),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'sequence' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:20',
                Rule::unique('academic_terms', 'sequence')->where('academic_year_id', $academicYearId)->ignore($termId),
            ],
            'starts_on' => ['sometimes', 'required', 'date'],
            'ends_on' => ['sometimes', 'required', 'date', 'after:starts_on'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['academic_year_id', 'code', 'name', 'sequence', 'starts_on', 'ends_on', 'status', 'is_active'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string|int|bool> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['academic_year_id', 'code', 'name', 'starts_on', 'ends_on', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = (string) $validated[$field];
            }
        }

        if (array_key_exists('sequence', $validated)) {
            $changes['sequence'] = (int) $validated['sequence'];
        }

        if (array_key_exists('is_active', $validated)) {
            $changes['is_active'] = (bool) $validated['is_active'];
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed'];
    }
}
