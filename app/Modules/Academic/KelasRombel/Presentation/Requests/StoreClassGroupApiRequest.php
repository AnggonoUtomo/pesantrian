<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassGroupData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreClassGroupApiRequest extends FormRequest
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
            'academic_term_id' => ['required', 'ulid', Rule::exists('academic_terms', 'id')],
            'unit_id' => ['required', 'ulid', Rule::exists('organization_units', 'id')],
            'curriculum_id' => ['nullable', 'ulid', Rule::exists('academic_curricula', 'id')],
            'class_level_id' => ['required', 'ulid', Rule::exists('class_levels', 'id')],
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('class_groups', 'code')
                    ->where('unit_id', (string) $this->input('unit_id'))
                    ->where('academic_year_id', (string) $this->input('academic_year_id'))
                    ->where('academic_term_id', (string) $this->input('academic_term_id')),
            ],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $termYear = DB::table('academic_terms')->where('id', $this->input('academic_term_id'))->value('academic_year_id');
            if ($termYear !== null && $termYear !== $this->input('academic_year_id')) {
                $validator->errors()->add('academic_term_id', 'Term akademik harus berada dalam tahun akademik yang sama.');
            }

            $levelUnit = DB::table('class_levels')->where('id', $this->input('class_level_id'))->value('unit_id');
            if ($levelUnit !== null && $levelUnit !== $this->input('unit_id')) {
                $validator->errors()->add('class_level_id', 'Tingkat kelas harus berada dalam unit yang sama.');
            }
        });
    }

    public function toData(): UpsertClassGroupData
    {
        $data = $this->validated();

        return new UpsertClassGroupData(
            academicYearId: (string) $data['academic_year_id'],
            academicTermId: (string) $data['academic_term_id'],
            unitId: (string) $data['unit_id'],
            curriculumId: isset($data['curriculum_id']) ? (string) $data['curriculum_id'] : null,
            classLevelId: (string) $data['class_level_id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            capacity: isset($data['capacity']) ? (int) $data['capacity'] : null,
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
