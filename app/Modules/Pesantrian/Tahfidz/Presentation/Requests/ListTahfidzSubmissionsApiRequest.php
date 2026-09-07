<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListTahfidzSubmissionsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:program_id,student_id,supervisor_id,academic_period_id,type,status,date_from,date_to'],
            'filter.program_id' => ['nullable', 'ulid', Rule::exists('tahfidz_programs', 'id')],
            'filter.student_id' => ['nullable', 'ulid', Rule::exists('students', 'id')],
            'filter.supervisor_id' => ['nullable', 'ulid', Rule::exists('employees', 'id')],
            'filter.academic_period_id' => ['nullable', 'ulid', Rule::exists('academic_terms', 'id')],
            'filter.type' => ['nullable', 'string', Rule::in(['new_memorization', 'murojaah'])],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'accepted', 'needs_revision', 'void'])],
            'filter.date_from' => ['nullable', 'date_format:Y-m-d'],
            'filter.date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:filter.date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'submission_date', '-submission_date', 'student_name', '-student_name', 'supervisor_name', '-supervisor_name', 'type', '-type', 'status', '-status'])],
        ];
    }

    public function toFilter(): TahfidzListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-submission_date');

        return new TahfidzListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            programId: isset($filters['program_id']) ? (string) $filters['program_id'] : null,
            studentId: isset($filters['student_id']) ? (string) $filters['student_id'] : null,
            supervisorId: isset($filters['supervisor_id']) ? (string) $filters['supervisor_id'] : null,
            academicPeriodId: isset($filters['academic_period_id']) ? (string) $filters['academic_period_id'] : null,
            type: isset($filters['type']) ? (string) $filters['type'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            dateFrom: isset($filters['date_from']) ? (string) $filters['date_from'] : null,
            dateTo: isset($filters['date_to']) ? (string) $filters['date_to'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
