<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentDisciplineCaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'ulid'],
            'category_id' => ['sometimes', 'ulid'],
            'severity' => ['sometimes', 'string', Rule::in(['minor', 'moderate', 'major'])],
            'points' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:999'],
            'occurred_at' => ['sometimes', 'date'],
            'location' => ['sometimes', 'nullable', 'string', 'max:180'],
            'description' => ['sometimes', 'string', 'min:5', 'max:5000'],
            'assigned_employee_id' => ['sometimes', 'nullable', 'ulid'],
            'revision_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function toData(): StudentDisciplineCaseMutationData
    {
        $data = $this->validated();

        return new StudentDisciplineCaseMutationData(
            studentId: isset($data['student_id']) ? (string) $data['student_id'] : null,
            studentNo: null,
            studentName: null,
            unitId: null,
            categoryId: isset($data['category_id']) ? (string) $data['category_id'] : null,
            categoryName: null,
            severity: isset($data['severity']) ? (string) $data['severity'] : null,
            points: array_key_exists('points', $data) && $data['points'] !== null ? (int) $data['points'] : null,
            occurredAt: isset($data['occurred_at']) ? (string) $data['occurred_at'] : null,
            location: isset($data['location']) ? (string) $data['location'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            assignedEmployeeId: isset($data['assigned_employee_id']) ? (string) $data['assigned_employee_id'] : null,
            assignedEmployeeName: null,
        );
    }

    public function revisionReason(): string
    {
        return trim((string) $this->validated('revision_reason'));
    }
}
