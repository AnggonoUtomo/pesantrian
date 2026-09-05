<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentAttendancesApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:date_from,date_to,context_type,context_id,status'],
            'filter.date_from' => ['nullable', 'date_format:Y-m-d'],
            'filter.date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:filter.date_from'],
            'filter.context_type' => ['nullable', 'string', Rule::in(['class_group', 'dormitory', 'activity'])],
            'filter.context_id' => ['nullable', 'ulid'],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'revised', 'void'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'attendance_date', '-attendance_date', 'session_code', '-session_code', 'session_name', '-session_name', 'context_type', '-context_type', 'status', '-status'])],
        ];
    }

    public function toFilter(): StudentAttendanceListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-attendance_date');

        return new StudentAttendanceListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            dateFrom: isset($filters['date_from']) ? (string) $filters['date_from'] : null,
            dateTo: isset($filters['date_to']) ? (string) $filters['date_to'] : null,
            contextType: isset($filters['context_type']) ? (string) $filters['context_type'] : null,
            contextId: isset($filters['context_id']) ? (string) $filters['context_id'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
