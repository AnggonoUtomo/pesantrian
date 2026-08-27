<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Requests;

use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListOrganizationUnitsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:status,type'],
            'filter.status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'filter.type' => ['nullable', 'string', Rule::in($this->types())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'name', '-name', 'code', '-code'])],
        ];
    }

    public function toFilter(): OrganizationUnitListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? 'name');

        return new OrganizationUnitListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            type: isset($filters['type']) ? (string) $filters['type'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }

    /** @return list<string> */
    private function types(): array
    {
        return ['foundation', 'pesantren', 'education_unit', 'operational_unit', 'dormitory', 'other'];
    }
}
