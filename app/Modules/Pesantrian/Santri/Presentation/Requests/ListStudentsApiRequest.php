<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Requests;

use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:status,primary_unit_id'],
            'filter.status' => ['nullable', 'string', Rule::in($this->statuses())],
            'filter.primary_unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'student_no', '-student_no', 'full_name', '-full_name', 'entry_date', '-entry_date'])],
        ];
    }

    public function toFilter(): StudentListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-created_at');

        return new StudentListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            primaryUnitId: isset($filters['primary_unit_id']) ? (string) $filters['primary_unit_id'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'inactive', 'transferred', 'graduated'];
    }
}
