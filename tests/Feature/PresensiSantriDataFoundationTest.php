<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceRevisionRecord;
use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the PresensiSantri foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('student_attendance_sessions'))->toBeTrue()
        ->and(Schema::hasColumns('student_attendance_sessions', [
            'id',
            'attendance_date',
            'context_type',
            'context_id',
            'context_name',
            'session_code',
            'session_name',
            'status',
            'submitted_at',
            'submitted_by',
            'voided_at',
            'voided_by',
            'void_reason',
            'created_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_attendance_entries'))->toBeTrue()
        ->and(Schema::hasColumns('student_attendance_entries', [
            'id',
            'session_id',
            'student_id',
            'student_no',
            'student_name',
            'status',
            'minutes_late',
            'note',
            'source_reference_type',
            'source_reference_id',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_attendance_revisions'))->toBeTrue()
        ->and(Schema::hasColumns('student_attendance_revisions', [
            'id',
            'session_id',
            'reason',
            'changed_by',
            'changed_at',
            'summary',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('guards duplicate sessions in the same date and context scope', function (): void {
    seedPresensiFoundationReferences();

    DB::table('student_attendance_sessions')->insert([
        'id' => '01K6ATTDATA00000000000001',
        'attendance_date' => '2026-09-05',
        'context_type' => 'class_group',
        'context_id' => '01K6ATTDATA00000000000010',
        'context_name' => 'Kelas X A',
        'session_code' => 'KBM-PAGI',
        'session_name' => 'KBM Pagi',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('student_attendance_sessions')->insert([
        'id' => '01K6ATTDATA00000000000002',
        'attendance_date' => '2026-09-05',
        'context_type' => 'class_group',
        'context_id' => '01K6ATTDATA00000000000010',
        'context_name' => 'Kelas X A',
        'session_code' => 'KBM-PAGI',
        'session_name' => 'KBM Pagi Duplikat',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('guards one attendance entry per session and student', function (): void {
    seedPresensiFoundationReferences();
    seedPresensiFoundationSession();

    DB::table('student_attendance_entries')->insert([
        'id' => '01K6ATTDATA00000000000003',
        'session_id' => '01K6ATTDATA00000000000001',
        'student_id' => '01K6ATTDATA00000000000020',
        'student_no' => 'NIS-PS-101',
        'student_name' => 'Ahmad Foundation',
        'status' => 'present',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('student_attendance_entries')->insert([
        'id' => '01K6ATTDATA00000000000004',
        'session_id' => '01K6ATTDATA00000000000001',
        'student_id' => '01K6ATTDATA00000000000020',
        'student_no' => 'NIS-PS-101',
        'student_name' => 'Ahmad Foundation',
        'status' => 'late',
        'minutes_late' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('keeps revision history rows for the same attendance session', function (): void {
    seedPresensiFoundationReferences();
    seedPresensiFoundationSession();

    DB::table('student_attendance_revisions')->insert([
        [
            'id' => '01K6ATTDATA00000000000005',
            'session_id' => '01K6ATTDATA00000000000001',
            'reason' => 'Koreksi status terlambat.',
            'changed_at' => '2026-09-05 10:00:00',
            'summary' => json_encode(['changed_entries' => 1], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTDATA00000000000006',
            'session_id' => '01K6ATTDATA00000000000001',
            'reason' => 'Koreksi catatan izin.',
            'changed_at' => '2026-09-05 11:00:00',
            'summary' => json_encode(['changed_entries' => 1], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('student_attendance_revisions')->where('session_id', '01K6ATTDATA00000000000001')->count())->toBe(2);
});

it('creates attendance records through local factories', function (): void {
    seedPresensiFoundationReferences();

    $session = StudentAttendanceSessionRecord::factory()->create([
        'context_id' => '01K6ATTDATA00000000000010',
        'context_name' => 'Kelas X A',
    ]);
    $entry = StudentAttendanceEntryRecord::factory()->create([
        'session_id' => $session->id,
        'student_id' => '01K6ATTDATA00000000000020',
        'student_no' => 'NIS-PS-101',
        'student_name' => 'Ahmad Foundation',
    ]);
    $revision = StudentAttendanceRevisionRecord::factory()->create([
        'session_id' => $session->id,
    ]);

    expect($session->getKey())->toBeString()
        ->and($entry->session->is($session))->toBeTrue()
        ->and($revision->session->is($session))->toBeTrue();
});

function seedPresensiFoundationReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K6ATTDATA00000000000009',
        'code' => 'MA',
        'name' => 'MA Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K6ATTDATA00000000000011',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K6ATTDATA00000000000012',
        'academic_year_id' => '01K6ATTDATA00000000000011',
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

    DB::table('class_levels')->insert([
        'id' => '01K6ATTDATA00000000000013',
        'unit_id' => '01K6ATTDATA00000000000009',
        'code' => 'X',
        'name' => 'Kelas X',
        'sequence' => 10,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        'id' => '01K6ATTDATA00000000000010',
        'academic_year_id' => '01K6ATTDATA00000000000011',
        'academic_term_id' => '01K6ATTDATA00000000000012',
        'unit_id' => '01K6ATTDATA00000000000009',
        'class_level_id' => '01K6ATTDATA00000000000013',
        'code' => 'X-A',
        'name' => 'Kelas X A',
        'capacity' => 30,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01K6ATTDATA00000000000020',
        'student_no' => 'NIS-PS-101',
        'full_name' => 'Ahmad Foundation',
        'primary_unit_id' => '01K6ATTDATA00000000000009',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedPresensiFoundationSession(): void
{
    DB::table('student_attendance_sessions')->insert([
        'id' => '01K6ATTDATA00000000000001',
        'attendance_date' => '2026-09-05',
        'context_type' => 'class_group',
        'context_id' => '01K6ATTDATA00000000000010',
        'context_name' => 'Kelas X A',
        'session_code' => 'KBM-PAGI',
        'session_name' => 'KBM Pagi',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
