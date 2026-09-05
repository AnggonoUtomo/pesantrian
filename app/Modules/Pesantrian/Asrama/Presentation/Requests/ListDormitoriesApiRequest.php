<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListDormitoriesApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:unit_id,gender_policy,status,archived'],
            'filter.unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'filter.gender_policy' => ['nullable', 'string', Rule::in(['male', 'female', 'mixed', 'unspecified'])],
            'filter.status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'filter.archived' => ['nullable', 'string', Rule::in(['active', 'archived'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'code', '-code', 'name', '-name', 'gender_policy', '-gender_policy', 'capacity', '-capacity', 'occupied_count', '-occupied_count', 'status', '-status'])],
        ];
    }

    public function toFilter(): DormitoryListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-created_at');

        return new DormitoryListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            unitId: isset($filters['unit_id']) ? (string) $filters['unit_id'] : null,
            genderPolicy: isset($filters['gender_policy']) ? (string) $filters['gender_policy'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            archived: isset($filters['archived']) ? (string) $filters['archived'] : 'active',
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
