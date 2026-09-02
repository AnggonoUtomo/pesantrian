<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateClassLevelApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $levelId = (string) $this->route('level');
        $current = ClassLevelRecord::query()->find($levelId);
        $unitId = (string) ($this->input('unit_id') ?: ($current?->unit_id ?? ''));

        return [
            'unit_id' => ['sometimes', 'required', 'ulid', Rule::exists('organization_units', 'id')],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('class_levels', 'code')->where('unit_id', $unitId)->ignore($levelId),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'sequence' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:99',
                Rule::unique('class_levels', 'sequence')->where('unit_id', $unitId)->ignore($levelId),
            ],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['unit_id', 'code', 'name', 'sequence', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string|int> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['unit_id', 'code', 'name', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = (string) $validated[$field];
            }
        }

        if (array_key_exists('sequence', $validated)) {
            $changes['sequence'] = (int) $validated['sequence'];
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
