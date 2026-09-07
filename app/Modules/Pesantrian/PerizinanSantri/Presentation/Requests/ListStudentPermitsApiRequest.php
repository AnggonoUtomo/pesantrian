<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentPermitsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:date_from,date_to,permit_type,status,student_id,is_late'],
            'filter.date_from' => ['nullable', 'date_format:Y-m-d'],
            'filter.date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:filter.date_from'],
            'filter.permit_type' => ['nullable', 'string', Rule::in(['leave', 'home_visit', 'sick', 'activity'])],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'checked_out', 'returned', 'void'])],
            'filter.student_id' => ['nullable', 'ulid'],
            'filter.is_late' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'permit_no', '-permit_no', 'student_name', '-student_name', 'permit_type', '-permit_type', 'status', '-status', 'starts_at', '-starts_at', 'ends_at', '-ends_at'])],
        ];
    }

    public function toFilter(): StudentPermitListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-starts_at');
        $isLate = $filters['is_late'] ?? null;
        $search = isset($data['search']) ? trim((string) $data['search']) : null;

        return new StudentPermitListFilter(
            search: $search === '' ? null : $search,
            dateFrom: isset($filters['date_from']) ? (string) $filters['date_from'] : null,
            dateTo: isset($filters['date_to']) ? (string) $filters['date_to'] : null,
            permitType: isset($filters['permit_type']) ? (string) $filters['permit_type'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            studentId: isset($filters['student_id']) ? (string) $filters['student_id'] : null,
            isLate: $isLate === null ? null : filter_var($isLate, FILTER_VALIDATE_BOOLEAN),
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
