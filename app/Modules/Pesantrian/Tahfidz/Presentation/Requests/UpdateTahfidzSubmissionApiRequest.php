<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateTahfidzSubmissionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'program_id' => ['sometimes', 'required', 'ulid', Rule::exists('tahfidz_programs', 'id')],
            'target_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('tahfidz_targets', 'id')],
            'student_id' => ['sometimes', 'required', 'ulid', Rule::exists('students', 'id')],
            'supervisor_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('employees', 'id')],
            'submission_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'type' => ['sometimes', 'required', 'string', Rule::in($this->types())],
            'juz' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:30'],
            'surah' => ['sometimes', 'nullable', 'string', 'max:120'],
            'ayah_from' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'ayah_to' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
            'quality_note' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['program_id', 'target_id', 'student_id', 'supervisor_id', 'submission_date', 'type', 'juz', 'surah', 'ayah_from', 'ayah_to', 'status', 'quality_note'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }

            $this->validateProgramIsActive($validator);
            $this->validateTargetBelongsToPayload($validator);
            $this->validateAyahRange($validator);
        });
    }

    /** @return array<string, int|string|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['program_id', 'target_id', 'student_id', 'supervisor_id', 'submission_date', 'type', 'surah', 'status', 'quality_note'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        foreach (['juz', 'ayah_from', 'ayah_to'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (int) $validated[$field];
            }
        }

        return $changes;
    }

    private function validateProgramIsActive(Validator $validator): void
    {
        $programId = $this->input('program_id');

        if (! is_string($programId)) {
            return;
        }

        $program = DB::table('tahfidz_programs')
            ->where('id', $programId)
            ->select(['status', 'archived_at'])
            ->first();

        if ($program !== null && ($program->status !== 'active' || $program->archived_at !== null)) {
            $validator->errors()->add('program_id', 'Program tahfidz harus aktif.');
        }
    }

    private function validateTargetBelongsToPayload(Validator $validator): void
    {
        $targetId = $this->input('target_id');

        if (! is_string($targetId)) {
            return;
        }

        $target = DB::table('tahfidz_targets')
            ->where('id', $targetId)
            ->select(['program_id', 'student_id', 'status'])
            ->first();

        if ($target === null) {
            return;
        }

        if ($this->filled('program_id') && $target->program_id !== $this->input('program_id')) {
            $validator->errors()->add('target_id', 'Target hafalan harus berada pada program yang sama.');
        }

        if ($this->filled('student_id') && $target->student_id !== $this->input('student_id')) {
            $validator->errors()->add('target_id', 'Target hafalan harus milik santri yang sama.');
        }

        if ($target->status !== 'active') {
            $validator->errors()->add('target_id', 'Target hafalan harus aktif.');
        }
    }

    private function validateAyahRange(Validator $validator): void
    {
        if (! $this->filled('ayah_from') || ! $this->filled('ayah_to')) {
            return;
        }

        if ((int) $this->input('ayah_to') < (int) $this->input('ayah_from')) {
            $validator->errors()->add('ayah_to', 'Ayat akhir tidak boleh lebih kecil dari ayat awal.');
        }
    }

    /** @return list<string> */
    private function types(): array
    {
        return ['new_memorization', 'murojaah'];
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'submitted'];
    }
}
