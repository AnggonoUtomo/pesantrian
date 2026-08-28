<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateAcademicYearApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $yearId = (string) $this->route('year');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[0-9]{4}-[0-9]{4}$/', Rule::unique('academic_years', 'code')->ignore($yearId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'starts_on' => ['sometimes', 'required', 'date'],
            'ends_on' => ['sometimes', 'required', 'date', 'after:starts_on'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['code', 'name', 'starts_on', 'ends_on', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['code', 'name', 'starts_on', 'ends_on', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = (string) $validated[$field];
            }
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed'];
    }
}
