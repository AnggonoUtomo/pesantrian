<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Requests;

use App\Modules\Pesantrian\Santri\Application\DTO\UpsertStudentData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentApiRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'min:2', 'max:180'],
            'preferred_name' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female'])],
            'birth_place' => ['nullable', 'string', 'max:120'],
            'birth_date' => ['nullable', 'date'],
            'previous_school' => ['nullable', 'string', 'max:180'],
            'primary_unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'entry_date' => ['nullable', 'date'],
            'guardian_name' => ['required', 'string', 'min:2', 'max:180'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_relation' => ['nullable', 'string', Rule::in(['ayah', 'ibu', 'wali'])],
            'is_emergency_contact' => ['nullable', 'boolean'],
        ];
    }

    public function toData(): UpsertStudentData
    {
        $data = $this->validated();

        return new UpsertStudentData(
            fullName: (string) $data['full_name'],
            preferredName: isset($data['preferred_name']) ? (string) $data['preferred_name'] : null,
            gender: isset($data['gender']) ? (string) $data['gender'] : null,
            birthPlace: isset($data['birth_place']) ? (string) $data['birth_place'] : null,
            birthDate: isset($data['birth_date']) ? (string) $data['birth_date'] : null,
            previousSchool: isset($data['previous_school']) ? (string) $data['previous_school'] : null,
            primaryUnitId: isset($data['primary_unit_id']) ? (string) $data['primary_unit_id'] : null,
            entryDate: isset($data['entry_date']) ? (string) $data['entry_date'] : null,
            guardianName: (string) $data['guardian_name'],
            guardianPhone: isset($data['guardian_phone']) ? (string) $data['guardian_phone'] : null,
            guardianRelation: isset($data['guardian_relation']) ? (string) $data['guardian_relation'] : null,
            isEmergencyContact: (bool) ($data['is_emergency_contact'] ?? false),
        );
    }
}
