<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Requests;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzSubmissionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreTahfidzSubmissionApiRequest extends FormRequest
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
            'target_id' => ['nullable', 'ulid', Rule::exists('tahfidz_targets', 'id')],
            'student_id' => ['required', 'ulid', Rule::exists('students', 'id')],
            'supervisor_id' => ['nullable', 'ulid', Rule::exists('employees', 'id')],
            'submission_date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', 'string', Rule::in($this->types())],
            'juz' => ['nullable', 'integer', 'min:1', 'max:30'],
            'surah' => ['nullable', 'string', 'max:120'],
            'ayah_from' => ['nullable', 'integer', 'min:1', 'max:999'],
            'ayah_to' => ['nullable', 'integer', 'min:1', 'max:999'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
            'quality_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateProgramIsActive($validator);
            $this->validateTargetBelongsToPayload($validator);
            $this->validateMemorizationScope($validator);
            $this->validateAyahRange($validator);
        });
    }

    public function toData(): UpsertTahfidzSubmissionData
    {
        $data = $this->validated();

        return new UpsertTahfidzSubmissionData(
            programId: (string) $data['program_id'],
            targetId: isset($data['target_id']) ? (string) $data['target_id'] : null,
            studentId: (string) $data['student_id'],
            studentNo: '',
            studentName: '',
            supervisorId: isset($data['supervisor_id']) ? (string) $data['supervisor_id'] : null,
            supervisorName: null,
            submissionDate: (string) $data['submission_date'],
            type: (string) $data['type'],
            juz: isset($data['juz']) ? (int) $data['juz'] : null,
            surah: isset($data['surah']) ? (string) $data['surah'] : null,
            ayahFrom: isset($data['ayah_from']) ? (int) $data['ayah_from'] : null,
            ayahTo: isset($data['ayah_to']) ? (int) $data['ayah_to'] : null,
            status: (string) $data['status'],
            qualityNote: isset($data['quality_note']) ? (string) $data['quality_note'] : null,
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

        if ($target->program_id !== $this->input('program_id')) {
            $validator->errors()->add('target_id', 'Target hafalan harus berada pada program yang sama.');
        }

        if ($target->student_id !== $this->input('student_id')) {
            $validator->errors()->add('target_id', 'Target hafalan harus milik santri yang sama.');
        }

        if ($target->status !== 'active') {
            $validator->errors()->add('target_id', 'Target hafalan harus aktif.');
        }
    }

    private function validateMemorizationScope(Validator $validator): void
    {
        if ($this->filled('juz') || $this->filled('surah') || $this->filled('ayah_from') || $this->filled('ayah_to')) {
            return;
        }

        $validator->errors()->add('hafalan', 'Isi minimal salah satu juz, surah, atau ayat setoran.');
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
