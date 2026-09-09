<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentAchievementsApiRequest extends FormRequest
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
            'filter' => ['nullable', 'array:date_from,date_to,status,level,category_id,student_id,mentor_employee_id,academic_period_id'],
            'filter.date_from' => ['nullable', 'date_format:Y-m-d'],
            'filter.date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:filter.date_from'],
            'filter.status' => ['nullable', 'string', Rule::in(['draft', 'submitted', 'needs_revision', 'verified', 'void'])],
            'filter.level' => ['nullable', 'string', Rule::in(['internal', 'district', 'city', 'province', 'national', 'international'])],
            'filter.category_id' => ['nullable', 'ulid'],
            'filter.student_id' => ['nullable', 'ulid'],
            'filter.mentor_employee_id' => ['nullable', 'ulid'],
            'filter.academic_period_id' => ['nullable', 'ulid'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'achievement_no', '-achievement_no', 'student_name', '-student_name', 'status', '-status', 'level', '-level', 'achieved_on', '-achieved_on'])],
        ];
    }

    public function toFilter(): StudentAchievementListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-achieved_on');
        $search = isset($data['search']) ? trim((string) $data['search']) : null;

        return new StudentAchievementListFilter(
            search: $search === '' ? null : $search,
            dateFrom: isset($filters['date_from']) ? (string) $filters['date_from'] : null,
            dateTo: isset($filters['date_to']) ? (string) $filters['date_to'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            level: isset($filters['level']) ? (string) $filters['level'] : null,
            categoryId: isset($filters['category_id']) ? (string) $filters['category_id'] : null,
            studentId: isset($filters['student_id']) ? (string) $filters['student_id'] : null,
            mentorEmployeeId: isset($filters['mentor_employee_id']) ? (string) $filters['mentor_employee_id'] : null,
            academicPeriodId: isset($filters['academic_period_id']) ? (string) $filters['academic_period_id'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }
}
