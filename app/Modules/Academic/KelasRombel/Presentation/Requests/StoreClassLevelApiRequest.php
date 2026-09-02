<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassLevelData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreClassLevelApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'ulid', Rule::exists('organization_units', 'id')],
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('class_levels', 'code')->where('unit_id', (string) $this->input('unit_id')),
            ],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('class_levels', 'sequence')->where('unit_id', (string) $this->input('unit_id')),
            ],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function toData(): UpsertClassLevelData
    {
        $data = $this->validated();

        return new UpsertClassLevelData(
            unitId: (string) $data['unit_id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            sequence: (int) $data['sequence'],
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
