<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Requests;

use App\Modules\Organization\Organization\Application\DTO\UpsertOrganizationUnitData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrganizationUnitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('organization_units', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'type' => ['required', 'string', Rule::in(['foundation', 'pesantren', 'education_unit', 'operational_unit', 'dormitory', 'other'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'location_name' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function toData(): UpsertOrganizationUnitData
    {
        $data = $this->validated();

        return new UpsertOrganizationUnitData(
            parentId: isset($data['parent_id']) ? (string) $data['parent_id'] : null,
            code: (string) $data['code'],
            name: (string) $data['name'],
            type: (string) $data['type'],
            status: (string) $data['status'],
            locationName: isset($data['location_name']) ? (string) $data['location_name'] : null,
        );
    }
}
