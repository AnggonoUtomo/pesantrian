<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateTahfidzProgramApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $programId = (string) $this->route('program');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('tahfidz_programs', 'code')->ignore($programId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['code', 'name', 'description', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }

            $program = TahfidzProgramRecord::query()->find((string) $this->route('program'));

            if ($program instanceof TahfidzProgramRecord && $program->archived_at !== null) {
                $validator->errors()->add('program', 'Program tahfidz yang sudah diarsipkan tidak bisa diperbarui.');
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
        return ['active', 'inactive'];
    }
}
