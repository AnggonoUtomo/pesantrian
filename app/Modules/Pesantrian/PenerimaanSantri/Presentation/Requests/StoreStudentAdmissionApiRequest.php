<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\UpsertStudentAdmissionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentAdmissionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'registration_no' => ['prohibited'],
            'registration_period' => ['nullable', 'string', 'max:80'],
            'candidate_name' => ['required', 'string', 'min:2', 'max:180'],
            'candidate_gender' => ['nullable', 'string', Rule::in(['male', 'female'])],
            'candidate_birth_place' => ['nullable', 'string', 'max:120'],
            'candidate_birth_date' => ['nullable', 'date'],
            'previous_school' => ['nullable', 'string', 'max:180'],
            'target_unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'guardian_name' => ['required', 'string', 'min:2', 'max:180'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_relation' => ['nullable', 'string', Rule::in(['ayah', 'ibu', 'wali'])],
            'registration_fee_required' => ['nullable', 'boolean'],
            'registration_fee_amount' => ['nullable', 'numeric', 'min:0', 'required_if:registration_fee_required,true'],
            'registration_fee_status' => ['required', 'string', Rule::in(['not_required', 'pending', 'verified', 'rejected'])],
            'document_checklist' => ['nullable', 'array', 'max:20'],
            'document_checklist.*.type' => ['required_with:document_checklist', 'string', 'min:2', 'max:80'],
            'document_checklist.*.status' => ['required_with:document_checklist', 'string', Rule::in(['not_submitted', 'submitted', 'verified', 'rejected'])],
            'document_checklist.*.notes' => ['nullable', 'string', 'max:300'],
            'status' => ['required', 'string', Rule::in(['draft', 'submitted', 'verified'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toData(): UpsertStudentAdmissionData
    {
        $data = $this->validated();

        return new UpsertStudentAdmissionData(
            registrationPeriod: isset($data['registration_period']) ? (string) $data['registration_period'] : null,
            candidateName: (string) $data['candidate_name'],
            candidateGender: isset($data['candidate_gender']) ? (string) $data['candidate_gender'] : null,
            candidateBirthPlace: isset($data['candidate_birth_place']) ? (string) $data['candidate_birth_place'] : null,
            candidateBirthDate: isset($data['candidate_birth_date']) ? (string) $data['candidate_birth_date'] : null,
            previousSchool: isset($data['previous_school']) ? (string) $data['previous_school'] : null,
            targetUnitId: isset($data['target_unit_id']) ? (string) $data['target_unit_id'] : null,
            guardianName: (string) $data['guardian_name'],
            guardianPhone: isset($data['guardian_phone']) ? (string) $data['guardian_phone'] : null,
            guardianRelation: isset($data['guardian_relation']) ? (string) $data['guardian_relation'] : null,
            registrationFeeRequired: (bool) ($data['registration_fee_required'] ?? false),
            registrationFeeAmount: isset($data['registration_fee_amount']) ? (string) $data['registration_fee_amount'] : null,
            registrationFeeStatus: (string) $data['registration_fee_status'],
            documentChecklist: $this->documentChecklist($data),
            status: (string) $data['status'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, string>>|null
     */
    private function documentChecklist(array $data): ?array
    {
        if (! isset($data['document_checklist']) || ! is_array($data['document_checklist'])) {
            return null;
        }

        return array_map(
            static fn (array $item): array => [
                'type' => (string) $item['type'],
                'status' => (string) $item['status'],
                'notes' => isset($item['notes']) ? (string) $item['notes'] : '',
            ],
            $data['document_checklist'],
        );
    }
}
