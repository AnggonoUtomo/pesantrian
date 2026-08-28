<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListAcademicTermsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:academic_year_id,status,is_active'],
            'filter.academic_year_id' => ['nullable', 'ulid', Rule::exists('academic_years', 'id')],
            'filter.status' => ['nullable', 'string', Rule::in($this->statuses())],
            'filter.is_active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'code', '-code', 'sequence', '-sequence', 'starts_on', '-starts_on', 'name', '-name'])],
        ];
    }

    public function toFilter(): AcademicTermListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? 'sequence');

        return new AcademicTermListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            academicYearId: isset($filters['academic_year_id']) ? (string) $filters['academic_year_id'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            isActive: array_key_exists('is_active', $filters) ? (bool) $filters['is_active'] : null,
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
