<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzTargetData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreTahfidzTargetApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'program_id' => ['required', 'ulid', Rule::exists('tahfidz_programs', 'id')],
            'student_id' => ['required', 'ulid', Rule::exists('students', 'id')],
            'academic_period_id' => ['nullable', 'ulid', Rule::exists('academic_terms', 'id')],
            'target_juz' => ['nullable', 'integer', 'min:1', 'max:30'],
            'target_surah' => ['nullable', 'string', 'max:120'],
            'target_ayah_from' => ['nullable', 'integer', 'min:1', 'max:999'],
            'target_ayah_to' => ['nullable', 'integer', 'min:1', 'max:999'],
            'target_note' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateProgramIsActive($validator);
            $this->validateTargetScope($validator);
            $this->validateAyahRange($validator);
        });
    }

    public function toData(): UpsertTahfidzTargetData
    {
        $data = $this->validated();

        return new UpsertTahfidzTargetData(
            programId: (string) $data['program_id'],
            studentId: (string) $data['student_id'],
            studentNo: '',
            studentName: '',
            academicPeriodId: isset($data['academic_period_id']) ? (string) $data['academic_period_id'] : null,
            targetJuz: isset($data['target_juz']) ? (int) $data['target_juz'] : null,
            targetSurah: isset($data['target_surah']) ? (string) $data['target_surah'] : null,
            targetAyahFrom: isset($data['target_ayah_from']) ? (int) $data['target_ayah_from'] : null,
            targetAyahTo: isset($data['target_ayah_to']) ? (int) $data['target_ayah_to'] : null,
            targetNote: isset($data['target_note']) ? (string) $data['target_note'] : null,
            status: (string) $data['status'],
        );
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

    private function validateTargetScope(Validator $validator): void
    {
        if ($this->filled('target_juz') || $this->filled('target_surah') || $this->filled('target_ayah_from') || $this->filled('target_ayah_to')) {
            return;
        }

        $validator->errors()->add('target', 'Isi minimal salah satu target juz, surah, atau ayat.');
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
