<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentDisciplineCasesApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:date_from,date_to,status,severity,category_id,student_id,assigned_employee_id'],
            'filter.date_from' => ['nullable', 'date_format:Y-m-d'],
            'filter.date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:filter.date_from'],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'in_review', 'action_assigned', 'resolved', 'void'])],
            'filter.severity' => ['nullable', 'string', Rule::in(['minor', 'moderate', 'major'])],
            'filter.category_id' => ['nullable', 'ulid'],
            'filter.student_id' => ['nullable', 'ulid'],
            'filter.assigned_employee_id' => ['nullable', 'ulid'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'case_no', '-case_no', 'student_name', '-student_name', 'status', '-status', 'severity', '-severity', 'occurred_at', '-occurred_at'])],
        ];
    }

    public function toFilter(): StudentDisciplineCaseListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-occurred_at');
        $search = isset($data['search']) ? trim((string) $data['search']) : null;

        return new StudentDisciplineCaseListFilter(
            search: $search === '' ? null : $search,
            dateFrom: isset($filters['date_from']) ? (string) $filters['date_from'] : null,
            dateTo: isset($filters['date_to']) ? (string) $filters['date_to'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            severity: isset($filters['severity']) ? (string) $filters['severity'] : null,
            categoryId: isset($filters['category_id']) ? (string) $filters['category_id'] : null,
            studentId: isset($filters['student_id']) ? (string) $filters['student_id'] : null,
            assignedEmployeeId: isset($filters['assigned_employee_id']) ? (string) $filters['assigned_employee_id'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
