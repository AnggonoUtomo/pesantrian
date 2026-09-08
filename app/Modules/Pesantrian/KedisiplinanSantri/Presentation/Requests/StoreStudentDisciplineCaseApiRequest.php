<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentDisciplineCaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'ulid'],
            'category_id' => ['required', 'ulid'],
            'severity' => ['required', 'string', Rule::in(['minor', 'moderate', 'major'])],
            'points' => ['nullable', 'integer', 'min:0', 'max:999'],
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:5', 'max:5000'],
            'assigned_employee_id' => ['nullable', 'ulid'],
        ];
    }

    public function toData(): StudentDisciplineCaseMutationData
    {
        $data = $this->validated();

        return new StudentDisciplineCaseMutationData(
            studentId: (string) $data['student_id'],
            studentNo: null,
            studentName: null,
            unitId: null,
            categoryId: (string) $data['category_id'],
            categoryName: null,
            severity: (string) $data['severity'],
            points: isset($data['points']) ? (int) $data['points'] : null,
            occurredAt: (string) $data['occurred_at'],
            location: isset($data['location']) ? (string) $data['location'] : null,
            description: (string) $data['description'],
            assignedEmployeeId: isset($data['assigned_employee_id']) ? (string) $data['assigned_employee_id'] : null,
            assignedEmployeeName: null,
        );
    }
}
