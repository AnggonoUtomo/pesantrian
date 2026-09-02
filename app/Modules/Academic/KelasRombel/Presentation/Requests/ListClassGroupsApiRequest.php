<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListClassGroupsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:academic_year_id,academic_term_id,unit_id,curriculum_id,status,archived'],
            'filter.academic_year_id' => ['nullable', 'ulid', Rule::exists('academic_years', 'id')],
            'filter.academic_term_id' => ['nullable', 'ulid', Rule::exists('academic_terms', 'id')],
            'filter.unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'filter.curriculum_id' => ['nullable', 'ulid', Rule::exists('academic_curricula', 'id')],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'active', 'closed', 'archived'])],
            'filter.archived' => ['nullable', 'string', Rule::in(['active', 'archived'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'code', '-code', 'name', '-name', 'capacity', '-capacity', 'status', '-status'])],
        ];
    }

    public function toFilter(): ClassGroupListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-created_at');

        return new ClassGroupListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            academicYearId: isset($filters['academic_year_id']) ? (string) $filters['academic_year_id'] : null,
            academicTermId: isset($filters['academic_term_id']) ? (string) $filters['academic_term_id'] : null,
            unitId: isset($filters['unit_id']) ? (string) $filters['unit_id'] : null,
            curriculumId: isset($filters['curriculum_id']) ? (string) $filters['curriculum_id'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            archived: isset($filters['archived']) ? (string) $filters['archived'] : 'active',
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
