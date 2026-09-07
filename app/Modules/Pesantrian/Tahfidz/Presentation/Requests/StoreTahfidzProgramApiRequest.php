<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzProgramData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTahfidzProgramApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('tahfidz_programs', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function toData(): UpsertTahfidzProgramData
    {
        $data = $this->validated();

        return new UpsertTahfidzProgramData(
            code: (string) $data['code'],
            name: (string) $data['name'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'inactive'];
    }
}
