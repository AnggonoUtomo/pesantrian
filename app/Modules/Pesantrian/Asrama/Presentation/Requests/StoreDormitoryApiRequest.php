<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreDormitoryApiRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('dormitories', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'gender_policy' => ['required', 'string', Rule::in($this->genderPolicies())],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unitType = DB::table('organization_units')->where('id', $this->input('unit_id'))->value('type');

            if ($unitType !== null && $unitType !== 'dormitory') {
                $validator->errors()->add('unit_id', 'Unit organisasi harus bertipe asrama.');
            }
        });
    }

    public function toData(): UpsertDormitoryData
    {
        $data = $this->validated();

        return new UpsertDormitoryData(
            unitId: (string) $data['unit_id'],
            code: (string) $data['code'],
            name: (string) $data['name'],
            genderPolicy: (string) $data['gender_policy'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            status: (string) $data['status'],
        );
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
