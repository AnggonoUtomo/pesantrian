<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the PerizinanSantri foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('student_permits'))->toBeTrue()
        ->and(Schema::hasColumns('student_permits', [
            'id',
            'permit_no',
            'student_id',
            'student_no',
            'student_name',
            'permit_type',
            'starts_at',
            'ends_at',
            'destination',
            'reason',
            'guardian_name',
            'guardian_phone',
            'guardian_relation',
            'status',
            'submitted_at',
            'submitted_by',
            'reviewed_at',
            'reviewed_by',
            'review_note',
            'checked_out_at',
            'checked_out_by',
            'returned_at',
            'returned_by',
            'return_note',
            'voided_at',
            'voided_by',
            'void_reason',
            'created_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_permit_revisions'))->toBeTrue()
        ->and(Schema::hasColumns('student_permit_revisions', [
            'id',
            'permit_id',
            'reason',
            'changed_by',
            'changed_at',
            'summary',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('student_permits', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_permit_revisions', 'id'))->toBeIn(['string', 'varchar']);
});

it('guards duplicate permit numbers', function (): void {
    seedPerizinanFoundationStudent();

    DB::table('student_permits')->insert([
        'id' => '01KPERMITDATA000000000001',
        'permit_no' => 'IZN-000001',
        'student_id' => '01KPERMITDATA000000000010',
        'student_no' => 'NIS-IZN-101',
        'student_name' => 'Ahmad Perizinan',
        'permit_type' => 'home_visit',
        'starts_at' => '2026-09-10 08:00:00',
        'ends_at' => '2026-09-10 17:00:00',
        'reason' => 'Pulang bersama wali.',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::table('student_permits')->insert([
        'id' => '01KPERMITDATA000000000002',
        'permit_no' => 'IZN-000001',
        'student_id' => '01KPERMITDATA000000000010',
        'student_no' => 'NIS-IZN-101',
        'student_name' => 'Ahmad Perizinan',
        'permit_type' => 'leave',
        'starts_at' => '2026-09-11 08:00:00',
        'ends_at' => '2026-09-11 12:00:00',
        'reason' => 'Izin keluar area pesantren.',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('keeps revision history rows for the same permit', function (): void {
    seedPerizinanFoundationStudent();
    seedPerizinanFoundationPermit();

    DB::table('student_permit_revisions')->insert([
        [
            'id' => '01KPERMITDATA000000000004',
            'permit_id' => '01KPERMITDATA000000000001',
            'reason' => 'Koreksi jam kembali.',
            'changed_at' => '2026-09-10 09:00:00',
            'summary' => json_encode(['changed_fields' => ['ends_at']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPERMITDATA000000000005',
            'permit_id' => '01KPERMITDATA000000000001',
            'reason' => 'Koreksi catatan wali.',
            'changed_at' => '2026-09-10 10:00:00',
            'summary' => json_encode(['changed_fields' => ['guardian_phone']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('student_permit_revisions')->where('permit_id', '01KPERMITDATA000000000001')->count())->toBe(2);
});

it('creates permit records through local factories', function (): void {
    seedPerizinanFoundationStudent();

    $permit = StudentPermitRecord::factory()->create([
        'student_id' => '01KPERMITDATA000000000010',
        'student_no' => 'NIS-IZN-101',
        'student_name' => 'Ahmad Perizinan',
    ]);
    $revision = StudentPermitRevisionRecord::factory()->create([
        'permit_id' => $permit->id,
    ]);

    expect($permit->getKey())->toBeString()
        ->and($permit->starts_at->isBefore($permit->ends_at))->toBeTrue()
        ->and($revision->permit->is($permit))->toBeTrue()
        ->and($revision->summary)->toBe(['changed_fields' => ['status']]);
});

function seedPerizinanFoundationStudent(): void
{
    DB::table('students')->insert([
        'id' => '01KPERMITDATA000000000010',
        'student_no' => 'NIS-IZN-101',
        'full_name' => 'Ahmad Perizinan',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedPerizinanFoundationPermit(): void
{
    DB::table('student_permits')->insert([
        'id' => '01KPERMITDATA000000000001',
        'permit_no' => 'IZN-000001',
        'student_id' => '01KPERMITDATA000000000010',
        'student_no' => 'NIS-IZN-101',
        'student_name' => 'Ahmad Perizinan',
        'permit_type' => 'home_visit',
        'starts_at' => '2026-09-10 08:00:00',
        'ends_at' => '2026-09-10 17:00:00',
        'reason' => 'Pulang bersama wali.',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
