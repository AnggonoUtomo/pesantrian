<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateTahfidzTargetApiRequest extends FormRequest
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
            'student_id' => ['sometimes', 'required', 'ulid', Rule::exists('students', 'id')],
            'academic_period_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('academic_terms', 'id')],
            'target_juz' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:30'],
            'target_surah' => ['sometimes', 'nullable', 'string', 'max:120'],
            'target_ayah_from' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'target_ayah_to' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'target_note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['program_id', 'student_id', 'academic_period_id', 'target_juz', 'target_surah', 'target_ayah_from', 'target_ayah_to', 'target_note', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }

            $this->validateProgramIsActive($validator);
            $this->validateAyahRange($validator);
        });
    }

    /** @return array<string, int|string|null> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['program_id', 'student_id', 'academic_period_id', 'target_surah', 'target_note', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = $validated[$field] === null ? null : (string) $validated[$field];
            }
        }

        foreach (['target_juz', 'target_ayah_from', 'target_ayah_to'] as $field) {
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

    private function validateAyahRange(Validator $validator): void
    {
        if (! $this->filled('target_ayah_from') || ! $this->filled('target_ayah_to')) {
            return;
        }

        if ((int) $this->input('target_ayah_to') < (int) $this->input('target_ayah_from')) {
            $validator->errors()->add('target_ayah_to', 'Ayat akhir tidak boleh lebih kecil dari ayat awal.');
        }
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'completed', 'cancelled'];
    }
}
