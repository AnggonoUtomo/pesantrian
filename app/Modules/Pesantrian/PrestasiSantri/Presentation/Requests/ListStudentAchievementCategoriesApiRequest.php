<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentAchievementCategoriesApiRequest extends FormRequest
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

    public function toFilter(): StudentAchievementCategoryListFilter
    {
        $data = $this->validated();
        $search = isset($data['search']) ? trim((string) $data['search']) : null;

        return new StudentAchievementCategoryListFilter(
            search: $search === '' ? null : $search,
            status: isset($data['status']) ? (string) $data['status'] : null,
        );
    }
}
