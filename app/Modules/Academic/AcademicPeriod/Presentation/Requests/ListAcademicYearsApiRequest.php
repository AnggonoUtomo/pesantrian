<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListAcademicYearsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:status'],
            'filter.status' => ['nullable', 'string', Rule::in($this->statuses())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'code', '-code', 'starts_on', '-starts_on', 'name', '-name'])],
        ];
    }

    public function toFilter(): AcademicYearListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-starts_on');

        return new AcademicYearListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed'];
    }
}
