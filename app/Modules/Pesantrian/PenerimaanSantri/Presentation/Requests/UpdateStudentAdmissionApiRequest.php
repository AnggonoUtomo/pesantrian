<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateStudentAdmissionApiRequest extends FormRequest
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
            'registration_period' => ['sometimes', 'nullable', 'string', 'max:80'],
            'candidate_name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'candidate_gender' => ['sometimes', 'nullable', 'string', Rule::in(['male', 'female'])],
            'candidate_birth_place' => ['sometimes', 'nullable', 'string', 'max:120'],
            'candidate_birth_date' => ['sometimes', 'nullable', 'date'],
            'previous_school' => ['sometimes', 'nullable', 'string', 'max:180'],
            'target_unit_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'guardian_name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'guardian_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'guardian_relation' => ['sometimes', 'nullable', 'string', Rule::in(['ayah', 'ibu', 'wali'])],
            'registration_fee_required' => ['sometimes', 'boolean'],
            'registration_fee_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'registration_fee_status' => ['sometimes', 'required', 'string', Rule::in(['not_required', 'pending', 'verified', 'rejected'])],
            'document_checklist' => ['sometimes', 'nullable', 'array', 'max:20'],
            'document_checklist.*.type' => ['required_with:document_checklist', 'string', 'min:2', 'max:80'],
            'document_checklist.*.status' => ['required_with:document_checklist', 'string', Rule::in(['not_submitted', 'submitted', 'verified', 'rejected'])],
            'document_checklist.*.notes' => ['nullable', 'string', 'max:300'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'submitted', 'verified'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny([
                'registration_period',
                'candidate_name',
                'candidate_gender',
                'candidate_birth_place',
                'candidate_birth_date',
                'previous_school',
                'target_unit_id',
                'guardian_name',
                'guardian_phone',
                'guardian_relation',
                'registration_fee_required',
                'registration_fee_amount',
                'registration_fee_status',
                'document_checklist',
                'status',
                'notes',
            ])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, mixed> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach ([
            'registration_period',
            'candidate_name',
            'candidate_gender',
            'candidate_birth_place',
            'candidate_birth_date',
            'previous_school',
            'target_unit_id',
            'guardian_name',
            'guardian_phone',
            'guardian_relation',
            'registration_fee_required',
            'registration_fee_amount',
            'registration_fee_status',
            'document_checklist',
            'status',
            'notes',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $field === 'document_checklist'
                    ? $this->documentChecklist($validated)
                    : $validated[$field];
            }
        }

        return $changes;
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
