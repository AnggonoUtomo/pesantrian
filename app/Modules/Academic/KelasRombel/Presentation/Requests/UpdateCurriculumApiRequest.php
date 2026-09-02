<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateCurriculumApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $curriculumId = (string) $this->route('curriculum');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('academic_curricula', 'code')->ignore($curriculumId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['code', 'name', 'description', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['code', 'name', 'description', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
