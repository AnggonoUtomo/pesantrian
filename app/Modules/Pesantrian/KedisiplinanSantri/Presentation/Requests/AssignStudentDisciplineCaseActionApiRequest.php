<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignStudentDisciplineCaseActionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'action_plan' => ['required', 'string', 'min:5', 'max:2000'],
            'assigned_employee_id' => ['nullable', 'ulid'],
        ];
    }

    public function actionPlan(): string
    {
        return trim((string) $this->validated('action_plan'));
    }

    public function assignedEmployeeId(): ?string
    {
        $value = $this->validated('assigned_employee_id');

        return is_string($value) ? $value : null;
    }
}
