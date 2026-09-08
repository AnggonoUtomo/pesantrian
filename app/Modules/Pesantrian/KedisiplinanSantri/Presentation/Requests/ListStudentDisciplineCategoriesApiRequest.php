<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentDisciplineCategoriesApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['active', 'archived'])],
        ];
    }

    public function toFilter(): StudentDisciplineCategoryListFilter
    {
        $data = $this->validated();
        $search = isset($data['search']) ? trim((string) $data['search']) : null;

        return new StudentDisciplineCategoryListFilter(
            search: $search === '' ? null : $search,
            status: isset($data['status']) ? (string) $data['status'] : null,
        );
    }
}
