<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateStudentApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_no' => ['prohibited'],
            'admission_id' => ['prohibited'],
            'registration_no' => ['prohibited'],
            'status' => ['prohibited'],
            'full_name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'preferred_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'gender' => ['sometimes', 'nullable', 'string', Rule::in(['male', 'female'])],
            'birth_place' => ['sometimes', 'nullable', 'string', 'max:120'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'previous_school' => ['sometimes', 'nullable', 'string', 'max:180'],
            'primary_unit_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'entry_date' => ['sometimes', 'nullable', 'date'],
            'guardian_name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'guardian_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'guardian_relation' => ['sometimes', 'nullable', 'string', Rule::in(['ayah', 'ibu', 'wali'])],
            'is_emergency_contact' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'full_name',
                'preferred_name',
                'gender',
                'birth_place',
                'birth_date',
                'previous_school',
                'primary_unit_id',
                'entry_date',
                'guardian_name',
                'guardian_phone',
                'guardian_relation',
                'is_emergency_contact',
            ])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, mixed> */
    public function studentChanges(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach ([
            'full_name',
            'preferred_name',
            'gender',
            'birth_place',
            'birth_date',
            'previous_school',
            'primary_unit_id',
            'entry_date',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field];
            }
        }

        return $changes;
    }

    /** @return array<string, mixed> */
    public function guardianChanges(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach ([
            'guardian_name',
            'guardian_phone',
            'guardian_relation',
            'is_emergency_contact',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field];
            }
        }

        return $changes;
    }
}
