<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceSessionMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreStudentAttendanceApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'context_type' => ['required', 'string', Rule::in(['class_group', 'dormitory', 'activity'])],
            'context_id' => ['nullable', 'ulid'],
            'context_name' => ['required', 'string', 'max:150'],
            'session_code' => ['required', 'string', 'max:50'],
            'session_name' => ['required', 'string', 'max:150'],
            'entries' => ['nullable', 'array'],
            'entries.*.student_id' => ['required', 'ulid', 'distinct'],
            'entries.*.status' => ['required', 'string', Rule::in(['present', 'late', 'excused', 'sick', 'absent'])],
            'entries.*.minutes_late' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'entries.*.note' => ['nullable', 'string', 'max:255'],
            'entries.*.source_reference_type' => ['nullable', 'string', 'max:80'],
            'entries.*.source_reference_id' => ['nullable', 'ulid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(fn (Validator $validator): mixed => $this->validateLateMinutes($validator));
    }

    public function toData(): StudentAttendanceSessionMutationData
    {
        $data = $this->validated();

        return new StudentAttendanceSessionMutationData(
            attendanceDate: (string) $data['attendance_date'],
            contextType: (string) $data['context_type'],
            contextId: isset($data['context_id']) ? (string) $data['context_id'] : null,
            contextName: (string) $data['context_name'],
            sessionCode: (string) $data['session_code'],
            sessionName: (string) $data['session_name'],
        );
    }

    /** @return list<array<string, mixed>> */
    public function entries(): array
    {
        $data = $this->validated();

        return array_values(is_array($data['entries'] ?? null) ? $data['entries'] : []);
    }

    private function validateLateMinutes(Validator $validator): void
    {
        $entries = $this->input('entries', []);

        if (! is_array($entries)) {
            return;
        }

        foreach ($entries as $index => $entry) {
            if (! is_array($entry) || ($entry['status'] ?? null) !== 'late') {
                continue;
            }

            if (! isset($entry['minutes_late']) || (int) $entry['minutes_late'] < 1) {
                $validator->errors()->add("entries.$index.minutes_late", 'Menit terlambat wajib diisi untuk status terlambat.');
            }
        }
    }
}
