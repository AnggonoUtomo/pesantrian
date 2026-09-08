<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentDisciplineCategoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $categoryId = (string) $this->route('category');

        return [
            'code' => ['sometimes', 'string', 'max:40', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/', Rule::unique('student_discipline_categories', 'code')->ignore($categoryId)],
            'name' => ['sometimes', 'string', 'min:2', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'default_severity' => ['sometimes', 'string', Rule::in(['minor', 'moderate', 'major'])],
            'default_points' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    /** @return array<string, int|string|null> */
    public function changes(): array
    {
        $data = $this->validated();

        return collect($data)
            ->only(['code', 'name', 'description', 'default_severity', 'default_points'])
            ->map(static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)
            ->all();
    }
}
