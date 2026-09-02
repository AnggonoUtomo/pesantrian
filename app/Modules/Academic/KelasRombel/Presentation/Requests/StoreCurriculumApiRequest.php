<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Application\DTO\UpsertCurriculumData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCurriculumApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('academic_curricula', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function toData(): UpsertCurriculumData
    {
        $data = $this->validated();

        return new UpsertCurriculumData(
            code: (string) $data['code'],
            name: (string) $data['name'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed', 'archived'];
    }
}
