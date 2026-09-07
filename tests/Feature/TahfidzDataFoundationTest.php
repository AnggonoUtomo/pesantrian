<?php

declare(strict_types=1);

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the Tahfidz foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('tahfidz_programs'))->toBeTrue()
        ->and(Schema::hasColumns('tahfidz_programs', [
            'id',
            'code',
            'name',
            'description',
            'status',
            'created_by',
            'archived_at',
            'archived_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('tahfidz_targets'))->toBeTrue()
        ->and(Schema::hasColumns('tahfidz_targets', [
            'id',
            'program_id',
            'student_id',
            'student_no',
            'student_name',
            'academic_period_id',
            'period_label',
            'target_juz',
            'target_surah',
            'target_ayah_from',
            'target_ayah_to',
            'target_note',
            'status',
            'created_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('tahfidz_submissions'))->toBeTrue()
        ->and(Schema::hasColumns('tahfidz_submissions', [
            'id',
            'program_id',
            'target_id',
            'student_id',
            'student_no',
            'student_name',
            'supervisor_id',
            'supervisor_name',
            'submission_date',
            'type',
            'juz',
            'surah',
            'ayah_from',
            'ayah_to',
            'status',
            'quality_note',
            'created_by',
            'reviewed_at',
            'reviewed_by',
            'voided_at',
            'voided_by',
            'void_reason',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('tahfidz_submission_revisions'))->toBeTrue()
        ->and(Schema::hasColumns('tahfidz_submission_revisions', [
            'id',
            'submission_id',
            'reason',
            'changed_by',
            'changed_at',
            'summary',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('enforces unique Tahfidz program codes', function (): void {
    DB::table('tahfidz_programs')->insert([
        'id' => '01K7THFDZDATA000000000001',
        'code' => 'THF-REG',
        'name' => 'Tahfidz Reguler',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('tahfidz_programs')->insert([
        'id' => '01K7THFDZDATA000000000002',
        'code' => 'THF-REG',
        'name' => 'Tahfidz Reguler Duplikat',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('stores student, supervisor, and period snapshots for target and submission history', function (): void {
    seedTahfidzFoundationReferences();
    seedTahfidzFoundationProgram();

    DB::table('tahfidz_targets')->insert([
        'id' => '01K7THFDZDATA000000000011',
        'program_id' => '01K7THFDZDATA000000000010',
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'academic_period_id' => '01K7THFDZDATA000000000031',
        'period_label' => 'Semester Ganjil 2026/2027',
        'target_juz' => 1,
        'target_surah' => 'Al-Baqarah',
        'target_ayah_from' => 1,
        'target_ayah_to' => 20,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tahfidz_submissions')->insert([
        'id' => '01K7THFDZDATA000000000012',
        'program_id' => '01K7THFDZDATA000000000010',
        'target_id' => '01K7THFDZDATA000000000011',
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'supervisor_id' => '01K7THFDZDATA000000000041',
        'supervisor_name' => 'Ustadz Hasan Tahfidz',
        'submission_date' => '2026-09-07',
        'type' => 'new_memorization',
        'juz' => 1,
        'surah' => 'Al-Baqarah',
        'ayah_from' => 1,
        'ayah_to' => 5,
        'status' => 'submitted',
        'quality_note' => 'Setoran awal lancar.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->assertDatabaseHas('tahfidz_targets', [
        'id' => '01K7THFDZDATA000000000011',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'period_label' => 'Semester Ganjil 2026/2027',
    ]);

    $this->assertDatabaseHas('tahfidz_submissions', [
        'id' => '01K7THFDZDATA000000000012',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'supervisor_name' => 'Ustadz Hasan Tahfidz',
    ]);
});

it('keeps multiple revision history rows for a Tahfidz submission', function (): void {
    seedTahfidzFoundationReferences();
    seedTahfidzFoundationProgram();
    seedTahfidzFoundationSubmission();

    DB::table('tahfidz_submission_revisions')->insert([
        [
            'id' => '01K7THFDZDATA000000000061',
            'submission_id' => '01K7THFDZDATA000000000012',
            'reason' => 'Koreksi catatan kualitas.',
            'changed_at' => '2026-09-07 10:00:00',
            'summary' => json_encode(['field' => 'quality_note'], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K7THFDZDATA000000000062',
            'submission_id' => '01K7THFDZDATA000000000012',
            'reason' => 'Koreksi rentang ayat.',
            'changed_at' => '2026-09-07 11:00:00',
            'summary' => json_encode(['field' => 'ayah_to'], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('tahfidz_submission_revisions')->where('submission_id', '01K7THFDZDATA000000000012')->count())->toBe(2);
});

it('creates Tahfidz records through local factories', function (): void {
    seedTahfidzFoundationReferences();

    $program = TahfidzProgramRecord::factory()->create([
        'code' => 'THF-FAC',
        'name' => 'Tahfidz Factory',
    ]);
    $target = TahfidzTargetRecord::factory()->create([
        'program_id' => $program->id,
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'academic_period_id' => '01K7THFDZDATA000000000031',
        'period_label' => 'Semester Ganjil 2026/2027',
    ]);
    $submission = TahfidzSubmissionRecord::factory()->create([
        'program_id' => $program->id,
        'target_id' => $target->id,
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'supervisor_id' => '01K7THFDZDATA000000000041',
        'supervisor_name' => 'Ustadz Hasan Tahfidz',
    ]);
    $revision = TahfidzSubmissionRevisionRecord::factory()->create([
        'submission_id' => $submission->id,
        'summary' => ['field' => 'status'],
    ]);

    expect($program->getKey())->toBeString()
        ->and($target->program->is($program))->toBeTrue()
        ->and($submission->program->is($program))->toBeTrue()
        ->and($submission->target?->is($target))->toBeTrue()
        ->and($revision->submission->is($submission))->toBeTrue()
        ->and($revision->summary)->toBe(['field' => 'status']);
});

function seedTahfidzFoundationReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K7THFDZDATA000000000020',
        'code' => 'MA',
        'name' => 'MA Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'full_name' => 'Ahmad Hafidz',
        'primary_unit_id' => '01K7THFDZDATA000000000020',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K7THFDZDATA000000000030',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K7THFDZDATA000000000031',
        'academic_year_id' => '01K7THFDZDATA000000000030',
        'code' => '2026-1',
        'name' => 'Semester Ganjil 2026/2027',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        'id' => '01K7THFDZDATA000000000041',
        'employee_no' => 'PEG-THF-001',
        'name' => 'Ustadz Hasan Tahfidz',
        'employment_type' => 'teacher',
        'position' => 'Pembimbing Tahfidz',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedTahfidzFoundationProgram(): void
{
    DB::table('tahfidz_programs')->insert([
        'id' => '01K7THFDZDATA000000000010',
        'code' => 'THF-REG',
        'name' => 'Tahfidz Reguler',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedTahfidzFoundationSubmission(): void
{
    DB::table('tahfidz_targets')->insert([
        'id' => '01K7THFDZDATA000000000011',
        'program_id' => '01K7THFDZDATA000000000010',
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'academic_period_id' => '01K7THFDZDATA000000000031',
        'period_label' => 'Semester Ganjil 2026/2027',
        'target_juz' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tahfidz_submissions')->insert([
        'id' => '01K7THFDZDATA000000000012',
        'program_id' => '01K7THFDZDATA000000000010',
        'target_id' => '01K7THFDZDATA000000000011',
        'student_id' => '01K7THFDZDATA000000000021',
        'student_no' => 'NIS-THF-001',
        'student_name' => 'Ahmad Hafidz',
        'supervisor_id' => '01K7THFDZDATA000000000041',
        'supervisor_name' => 'Ustadz Hasan Tahfidz',
        'submission_date' => '2026-09-07',
        'type' => 'new_memorization',
        'juz' => 1,
        'status' => 'submitted',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
