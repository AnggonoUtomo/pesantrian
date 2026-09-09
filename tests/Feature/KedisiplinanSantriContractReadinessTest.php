<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListDisciplineOfficerCandidates;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListDisciplineStudentCandidates;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCaseCandidates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('can read active students for discipline cases through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01KDISCIPLINERDYUNIT001',
        'code' => 'MTK',
        'name' => 'MTs Kedisiplinan',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01KDISCIPLINERDYSTD001',
            'student_no' => 'NIS-DIS-001',
            'full_name' => 'Ahmad Tertib',
            'primary_unit_id' => '01KDISCIPLINERDYUNIT001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KDISCIPLINERDYSTD002',
            'student_no' => 'NIS-DIS-002',
            'full_name' => 'Santri Nonaktif',
            'primary_unit_id' => '01KDISCIPLINERDYUNIT001',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $query = app(ListDisciplineStudentCandidates::class);

    $options = $query->execute(
        primaryUnitId: '01KDISCIPLINERDYUNIT001',
        search: 'Ahmad',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KDISCIPLINERDYSTD001')
        ->and($options[0]->studentNo)->toBe('NIS-DIS-001')
        ->and($options[0]->fullName)->toBe('Ahmad Tertib')
        ->and($query->find('01KDISCIPLINERDYSTD002'))->toBeNull();
});

it('can read active officers for discipline cases through the HumanResource public contract', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01KDISCIPLINERDYEMP001',
            'employee_no' => 'PEG-DIS-001',
            'name' => 'Ustadz Karim Pembina',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KDISCIPLINERDYEMP002',
            'employee_no' => 'PEG-DIS-002',
            'name' => 'Pegawai Nonaktif',
            'employment_type' => 'staff',
            'position' => 'Pembina Kedisiplinan',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $query = app(ListDisciplineOfficerCandidates::class);

    $options = $query->execute(
        employmentType: 'staff',
        search: 'Karim',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KDISCIPLINERDYEMP001')
        ->and($options[0]->employeeNo)->toBe('PEG-DIS-001')
        ->and($options[0]->name)->toBe('Ustadz Karim Pembina')
        ->and($query->find('01KDISCIPLINERDYEMP002'))->toBeNull();
});

it('can build read-only discipline case candidates from attendance and permits without creating cases', function (): void {
    $references = seedDisciplineSignalReadinessData();

    $query = app(ListStudentDisciplineCaseCandidates::class);

    $candidates = $query->execute(
        dateFrom: '2026-09-10',
        dateTo: '2026-09-12',
    );

    expect($candidates)->toHaveCount(3)
        ->and(array_map(static fn ($candidate): string => $candidate->sourceType, $candidates))
        ->toEqualCanonicalizing(['attendance_late', 'attendance_absent', 'permit_late_return'])
        ->and(array_map(static fn ($candidate): bool => $candidate->requiresHumanReview, $candidates))
        ->toBe([true, true, true])
        ->and($candidates[0]->toArray())->toHaveKeys([
            'source_type',
            'source_id',
            'student_id',
            'student_no',
            'student_name',
            'occurred_at',
            'title',
            'description',
            'suggested_severity',
            'metadata',
            'requires_human_review',
        ])
        ->and(DB::table('student_discipline_cases')->count())->toBe(0);

    $studentCandidates = $query->execute(
        dateFrom: '2026-09-10',
        dateTo: '2026-09-12',
        studentId: $references['late_student_id'],
    );

    expect($studentCandidates)->toHaveCount(1)
        ->and($studentCandidates[0]->sourceType)->toBe('attendance_late')
        ->and($studentCandidates[0]->studentId)->toBe($references['late_student_id']);
});

it('does not import Infrastructure models from dependency modules', function (): void {
    $modulePath = base_path('app/Modules/Pesantrian/KedisiplinanSantri');
    $forbiddenImports = [
        'App\\Modules\\Academic\\KelasRombel\\Infrastructure\\',
        'App\\Modules\\HumanResource\\HumanResource\\Infrastructure\\',
        'App\\Modules\\Pesantrian\\Asrama\\Infrastructure\\',
        'App\\Modules\\Pesantrian\\PerizinanSantri\\Infrastructure\\',
        'App\\Modules\\Pesantrian\\PresensiSantri\\Infrastructure\\',
        'App\\Modules\\Pesantrian\\Santri\\Infrastructure\\',
    ];

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulePath));

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        foreach ($forbiddenImports as $forbiddenImport) {
            expect($contents)
                ->not->toContain($forbiddenImport, sprintf(
                    'KedisiplinanSantri must use public contracts instead of importing %s in %s.',
                    $forbiddenImport,
                    $file->getPathname(),
                ));
        }
    }
});

/** @return array{late_student_id: string, absent_student_id: string, permit_student_id: string} */
function seedDisciplineSignalReadinessData(): array
{
    $unitId = (string) Str::ulid();
    $lateStudentId = (string) Str::ulid();
    $absentStudentId = (string) Str::ulid();
    $permitStudentId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'code' => 'DIS-SIG',
        'name' => 'Unit Sinyal Kedisiplinan',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => $lateStudentId,
            'student_no' => 'NIS-DIS-SIG-001',
            'full_name' => 'Ali Terlambat',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $absentStudentId,
            'student_no' => 'NIS-DIS-SIG-002',
            'full_name' => 'Budi Alfa',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $permitStudentId,
            'student_no' => 'NIS-DIS-SIG-003',
            'full_name' => 'Cici Izin',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $sessionId = (string) Str::ulid();
    DB::table('student_attendance_sessions')->insert([
        'id' => $sessionId,
        'attendance_date' => '2026-09-10',
        'context_type' => 'activity',
        'context_id' => null,
        'context_name' => 'Apel Pagi',
        'session_code' => 'DIS-SIG-ATT',
        'session_name' => 'Apel Pagi Kedisiplinan',
        'status' => 'submitted',
        'submitted_at' => '2026-09-10 08:00:00',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('student_attendance_entries')->insert([
        [
            'id' => (string) Str::ulid(),
            'session_id' => $sessionId,
            'student_id' => $lateStudentId,
            'student_no' => 'NIS-DIS-SIG-001',
            'student_name' => 'Ali Terlambat',
            'status' => 'late',
            'minutes_late' => 20,
            'note' => 'Terlambat apel pagi.',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => (string) Str::ulid(),
            'session_id' => $sessionId,
            'student_id' => $absentStudentId,
            'student_no' => 'NIS-DIS-SIG-002',
            'student_name' => 'Budi Alfa',
            'status' => 'absent',
            'minutes_late' => null,
            'note' => 'Tidak hadir tanpa keterangan.',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => (string) Str::ulid(),
            'session_id' => $sessionId,
            'student_id' => $permitStudentId,
            'student_no' => 'NIS-DIS-SIG-003',
            'student_name' => 'Cici Izin',
            'status' => 'present',
            'minutes_late' => null,
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_permits')->insert([
        'id' => (string) Str::ulid(),
        'permit_no' => 'IZN-DIS-SIG-LATE',
        'student_id' => $permitStudentId,
        'student_no' => 'NIS-DIS-SIG-003',
        'student_name' => 'Cici Izin',
        'permit_type' => 'home_visit',
        'starts_at' => '2026-09-11 08:00:00',
        'ends_at' => '2026-09-11 17:00:00',
        'destination' => 'Rumah wali',
        'reason' => 'Keperluan keluarga.',
        'status' => 'returned',
        'submitted_at' => '2026-09-10 09:00:00',
        'reviewed_at' => '2026-09-10 10:00:00',
        'checked_out_at' => '2026-09-11 08:15:00',
        'returned_at' => '2026-09-11 18:30:00',
        'return_note' => 'Kembali terlambat 90 menit.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'late_student_id' => $lateStudentId,
        'absent_student_id' => $absentStudentId,
        'permit_student_id' => $permitStudentId,
    ];
}
