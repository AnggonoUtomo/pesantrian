<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSessionMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentAttendanceApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'attendance_date' => ['sometimes', 'date_format:Y-m-d'],
            'context_type' => ['sometimes', 'string', Rule::in(['class_group', 'dormitory', 'activity'])],
            'context_id' => ['nullable', 'ulid'],
            'context_name' => ['sometimes', 'string', 'max:150'],
            'session_code' => ['sometimes', 'string', 'max:50'],
            'session_name' => ['sometimes', 'string', 'max:150'],
        ];
    }

    public function toData(): StudentAttendanceSessionMutationData
    {
        $data = $this->validated();

        return new StudentAttendanceSessionMutationData(
            attendanceDate: isset($data['attendance_date']) ? (string) $data['attendance_date'] : null,
            contextType: isset($data['context_type']) ? (string) $data['context_type'] : null,
            contextId: array_key_exists('context_id', $data) && $data['context_id'] !== null ? (string) $data['context_id'] : null,
            contextName: isset($data['context_name']) ? (string) $data['context_name'] : null,
            sessionCode: isset($data['session_code']) ? (string) $data['session_code'] : null,
            sessionName: isset($data['session_name']) ? (string) $data['session_name'] : null,
        );
    }
}
