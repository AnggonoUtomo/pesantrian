<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateClassGroupApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $classGroupId = (string) $this->route('classGroup');
        $current = ClassGroupRecord::query()->find($classGroupId);
        $unitId = (string) ($this->input('unit_id') ?: ($current?->unit_id ?? ''));
        $academicYearId = (string) ($this->input('academic_year_id') ?: ($current?->academic_year_id ?? ''));
        $academicTermId = (string) ($this->input('academic_term_id') ?: ($current?->academic_term_id ?? ''));

        return [
            'academic_year_id' => ['sometimes', 'required', 'ulid', Rule::exists('academic_years', 'id')],
            'academic_term_id' => ['sometimes', 'required', 'ulid', Rule::exists('academic_terms', 'id')],
            'unit_id' => ['sometimes', 'required', 'ulid', Rule::exists('organization_units', 'id')],
            'curriculum_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('academic_curricula', 'id')],
            'class_level_id' => ['sometimes', 'required', 'ulid', Rule::exists('class_levels', 'id')],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('class_groups', 'code')
                    ->where('unit_id', $unitId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('academic_term_id', $academicTermId)
                    ->ignore($classGroupId),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:200'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'academic_year_id',
                'academic_term_id',
                'unit_id',
                'curriculum_id',
                'class_level_id',
                'code',
                'name',
                'capacity',
                'status',
            ])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }

            $classGroupId = (string) $this->route('classGroup');
            $current = ClassGroupRecord::query()->find($classGroupId);
            if (! $current instanceof ClassGroupRecord) {
                return;
            }

            $academicYearId = (string) ($this->input('academic_year_id') ?: $current->academic_year_id);
            $academicTermId = (string) ($this->input('academic_term_id') ?: $current->academic_term_id);
            $unitId = (string) ($this->input('unit_id') ?: $current->unit_id);
            $classLevelId = (string) ($this->input('class_level_id') ?: $current->class_level_id);

            $termYear = DB::table('academic_terms')->where('id', $academicTermId)->value('academic_year_id');
            if ($termYear !== null && $termYear !== $academicYearId) {
                $validator->errors()->add('academic_term_id', 'Term akademik harus berada dalam tahun akademik yang sama.');
            }

            $levelUnit = DB::table('class_levels')->where('id', $classLevelId)->value('unit_id');
            if ($levelUnit !== null && $levelUnit !== $unitId) {
                $validator->errors()->add('class_level_id', 'Tingkat kelas harus berada dalam unit yang sama.');
            }
        });
    }

    /** @return array<string, string|int|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['academic_year_id', 'academic_term_id', 'unit_id', 'curriculum_id', 'class_level_id', 'code', 'name', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        if (array_key_exists('capacity', $validated)) {
            $changes['capacity'] = $validated['capacity'] === null ? null : (int) $validated['capacity'];
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
