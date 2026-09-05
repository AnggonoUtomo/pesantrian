<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateDormitoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $dormitoryId = (string) $this->route('dormitory');

        return [
            'unit_id' => ['sometimes', 'required', 'ulid', Rule::exists('organization_units', 'id')],
            'code' => ['sometimes', 'required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('dormitories', 'code')->ignore($dormitoryId)],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:180'],
            'gender_policy' => ['sometimes', 'required', 'string', Rule::in($this->genderPolicies())],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['unit_id', 'code', 'name', 'gender_policy', 'description', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }

            $unitId = $this->input('unit_id');
            if ($unitId === null) {
                return;
            }

            $unitType = DB::table('organization_units')->where('id', $unitId)->value('type');

            if ($unitType !== null && $unitType !== 'dormitory') {
                $validator->errors()->add('unit_id', 'Unit organisasi harus bertipe asrama.');
            }

            $dormitory = DormitoryRecord::query()->find((string) $this->route('dormitory'));
            if ($dormitory instanceof DormitoryRecord && $dormitory->archived_at !== null) {
                $validator->errors()->add('dormitory', 'Asrama yang sudah diarsipkan tidak bisa diperbarui.');
            }
        });
    }

    /** @return array<string, string|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['unit_id', 'code', 'name', 'gender_policy', 'description', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        return $changes;
    }

    /** @return list<string> */
    private function genderPolicies(): array
    {
        return ['male', 'female', 'mixed', 'unspecified'];
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'inactive'];
    }
}
