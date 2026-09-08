<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\UpsertStudentDisciplineCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentDisciplineCategoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('student_discipline_categories', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'default_severity' => ['required', 'string', Rule::in(['minor', 'moderate', 'major'])],
            'default_points' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function toData(): UpsertStudentDisciplineCategoryData
    {
        $data = $this->validated();

        return new UpsertStudentDisciplineCategoryData(
            code: (string) $data['code'],
            name: (string) $data['name'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            defaultSeverity: (string) $data['default_severity'],
            defaultPoints: isset($data['default_points']) ? (int) $data['default_points'] : null,
        );
    }
}
