<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateOrganizationUnitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $unitId = (string) $this->route('unit');

        return [
            'parent_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('organization_units', 'id'), Rule::notIn([$unitId])],
            'code' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('organization_units', 'code')->ignore($unitId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['foundation', 'pesantren', 'education_unit', 'operational_unit', 'dormitory', 'other'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'location_name' => ['sometimes', 'nullable', 'string', 'max:180'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['parent_id', 'code', 'name', 'type', 'status', 'location_name'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['parent_id', 'code', 'name', 'type', 'status', 'location_name'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        return $changes;
    }
}
