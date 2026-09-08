<?php

declare(strict_types=1);

use App\Modules\Academic\KelasRombel\Application\Contracts\ActiveClassGroupRosterReader;
use App\Modules\Pesantrian\Asrama\Application\Contracts\ActiveDormitoryResidentReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\ApprovedStudentPermitReader;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\FindApprovedPermitForAttendance;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('can read active students for attendance through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000001',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000002',
            'student_no' => 'NIS-PS-001',
            'full_name' => 'Ahmad Presensi',
            'primary_unit_id' => '01K6ATTSNDRDY000000000001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $student = app(ActiveStudentReader::class)->findActive('01K6ATTSNDRDY000000000002');

    expect($student)->not->toBeNull()
        ->and($student?->studentNo)->toBe('NIS-PS-001')
        ->and($student?->fullName)->toBe('Ahmad Presensi');
});

it('can read active class group roster through the KelasRombel public contract', function (): void {
    seedClassGroupRosterReadinessData();

    $roster = app(ActiveClassGroupRosterReader::class)->studentsForClassGroup('01K6ATTSNDRDY000000000013');

    expect($roster)->toHaveCount(1)
        ->and($roster[0]->studentId)->toBe('01K6ATTSNDRDY000000000016')
        ->and($roster[0]->studentNo)->toBe('NIS-PS-002')
        ->and($roster[0]->studentName)->toBe('Budi Rombel')
        ->and($roster[0]->classGroupId)->toBe('01K6ATTSNDRDY000000000013')
        ->and($roster[0]->academicTermId)->toBe('01K6ATTSNDRDY000000000012');
});

it('can read active dormitory residents through the Asrama public contract', function (): void {
    seedDormitoryResidentReadinessData();

    $residents = app(ActiveDormitoryResidentReader::class)->residentsForDormitory('01K6ATTSNDRDY000000000023');

    expect($residents)->toHaveCount(1)
        ->and($residents[0]->studentId)->toBe('01K6ATTSNDRDY000000000026')
        ->and($residents[0]->studentNo)->toBe('NIS-PS-004')
        ->and($residents[0]->studentName)->toBe('Dewi Asrama')
        ->and($residents[0]->dormitoryId)->toBe('01K6ATTSNDRDY000000000023')
        ->and($residents[0]->roomId)->toBe('01K6ATTSNDRDY000000000024');
});

it('can read approved permits for attendance through the PerizinanSantri public contract', function (): void {
    $references = seedApprovedPermitReadinessData();

    $reader = app(ApprovedStudentPermitReader::class);
    $singlePermit = $reader->approvedForStudentOnDate($references['approved_student_id'], '2026-09-15');
    $permits = $reader->approvedForStudentsOnDate([
        $references['approved_student_id'],
        $references['checked_out_student_id'],
        $references['returned_student_id'],
        $references['submitted_student_id'],
        $references['void_student_id'],
    ], '2026-09-15');
    $presensiQuery = app(FindApprovedPermitForAttendance::class);

    expect($singlePermit)->not->toBeNull()
        ->and($singlePermit?->permitNo)->toBe('IZN-PRESENSI-APPROVED')
        ->and($singlePermit?->attendanceDate)->toBe('2026-09-15')
        ->and($singlePermit?->status)->toBe('approved')
        ->and($singlePermit?->toArray()['permit_no'])->toBe('IZN-PRESENSI-APPROVED')
        ->and($permits)->toHaveCount(3)
        ->and(array_map(static fn ($permit): string => $permit->permitNo, $permits))->toEqualCanonicalizing([
            'IZN-PRESENSI-APPROVED',
            'IZN-PRESENSI-CHECKEDOUT',
            'IZN-PRESENSI-RETURNED',
        ])
        ->and($presensiQuery->execute($references['checked_out_student_id'], '2026-09-15')?->permitNo)
        ->toBe('IZN-PRESENSI-CHECKEDOUT')
        ->and($presensiQuery->execute($references['submitted_student_id'], '2026-09-15'))
        ->toBeNull();
});

it('does not couple PresensiSantri to PerizinanSantri Infrastructure classes', function (): void {
    $modulePath = base_path('app/Modules/Pesantrian/PresensiSantri');
    $forbiddenImport = 'App\\Modules\\Pesantrian\\PerizinanSantri\\Infrastructure\\';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($modulePath));

    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        expect($contents)
            ->not->toContain($forbiddenImport, sprintf(
                'PresensiSantri must use PerizinanSantri public contracts instead of importing %s in %s.',
                $forbiddenImport,
                $file->getPathname(),
            ));
    }
});

function seedClassGroupRosterReadinessData(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000010',
        'code' => 'MA',
        'name' => 'MA Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01K6ATTSNDRDY000000000011',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K6ATTSNDRDY000000000012',
        'academic_year_id' => '01K6ATTSNDRDY000000000011',
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
        'id' => '01K6ATTSNDRDY000000000014',
        'unit_id' => '01K6ATTSNDRDY000000000010',
        'code' => 'X',
        'name' => 'Kelas X',
        'sequence' => 10,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('class_groups')->insert([
        'id' => '01K6ATTSNDRDY000000000013',
        'academic_year_id' => '01K6ATTSNDRDY000000000011',
        'academic_term_id' => '01K6ATTSNDRDY000000000012',
        'unit_id' => '01K6ATTSNDRDY000000000010',
        'class_level_id' => '01K6ATTSNDRDY000000000014',
        'code' => 'X-A',
        'name' => 'Kelas X A',
        'capacity' => 30,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000016',
            'student_no' => 'NIS-PS-002',
            'full_name' => 'Budi Rombel',
            'primary_unit_id' => '01K6ATTSNDRDY000000000010',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000017',
            'student_no' => 'NIS-PS-003',
            'full_name' => 'Cici Nonaktif',
            'primary_unit_id' => '01K6ATTSNDRDY000000000010',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('class_group_students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000018',
            'class_group_id' => '01K6ATTSNDRDY000000000013',
            'academic_term_id' => '01K6ATTSNDRDY000000000012',
            'student_id' => '01K6ATTSNDRDY000000000016',
            'student_no' => 'NIS-PS-002',
            'joined_on' => '2026-07-01',
            'left_on' => null,
            'status' => 'active',
            'active_period_student_key' => '01K6ATTSNDRDY000000000012:01K6ATTSNDRDY000000000016',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000019',
            'class_group_id' => '01K6ATTSNDRDY000000000013',
            'academic_term_id' => '01K6ATTSNDRDY000000000012',
            'student_id' => '01K6ATTSNDRDY000000000017',
            'student_no' => 'NIS-PS-003',
            'joined_on' => '2026-07-01',
            'left_on' => '2026-08-01',
            'status' => 'removed',
            'active_period_student_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}

/** @return array{approved_student_id: string, checked_out_student_id: string, returned_student_id: string, submitted_student_id: string, void_student_id: string} */
function seedApprovedPermitReadinessData(): array
{
    $unitId = (string) Str::ulid();
    $approvedStudentId = (string) Str::ulid();
    $checkedOutStudentId = (string) Str::ulid();
    $returnedStudentId = (string) Str::ulid();
    $submittedStudentId = (string) Str::ulid();
    $voidStudentId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'code' => 'PRS-IZN',
        'name' => 'Unit Presensi Izin',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => $approvedStudentId,
            'student_no' => 'NIS-PRS-IZN-001',
            'full_name' => 'Aisyah Approved Presensi',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $checkedOutStudentId,
            'student_no' => 'NIS-PRS-IZN-002',
            'full_name' => 'Budi Checked Out Presensi',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $returnedStudentId,
            'student_no' => 'NIS-PRS-IZN-003',
            'full_name' => 'Cici Returned Presensi',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $submittedStudentId,
            'student_no' => 'NIS-PRS-IZN-004',
            'full_name' => 'Dina Submitted Presensi',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => $voidStudentId,
            'student_no' => 'NIS-PRS-IZN-005',
            'full_name' => 'Eka Void Presensi',
            'primary_unit_id' => $unitId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_permits')->insert([
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-APPROVED',
            studentId: $approvedStudentId,
            studentNo: 'NIS-PRS-IZN-001',
            studentName: 'Aisyah Approved Presensi',
            status: 'approved',
            startsAt: '2026-09-15 08:00:00',
            endsAt: '2026-09-15 17:00:00',
        ),
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-CHECKEDOUT',
            studentId: $checkedOutStudentId,
            studentNo: 'NIS-PRS-IZN-002',
            studentName: 'Budi Checked Out Presensi',
            status: 'checked_out',
            startsAt: '2026-09-15 08:00:00',
            endsAt: '2026-09-15 17:00:00',
        ),
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-RETURNED',
            studentId: $returnedStudentId,
            studentNo: 'NIS-PRS-IZN-003',
            studentName: 'Cici Returned Presensi',
            status: 'returned',
            startsAt: '2026-09-15 08:00:00',
            endsAt: '2026-09-15 17:00:00',
            returnedAt: '2026-09-15 16:30:00',
        ),
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-SUBMITTED',
            studentId: $submittedStudentId,
            studentNo: 'NIS-PRS-IZN-004',
            studentName: 'Dina Submitted Presensi',
            status: 'submitted',
            startsAt: '2026-09-15 08:00:00',
            endsAt: '2026-09-15 17:00:00',
        ),
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-VOID',
            studentId: $voidStudentId,
            studentNo: 'NIS-PRS-IZN-005',
            studentName: 'Eka Void Presensi',
            status: 'void',
            startsAt: '2026-09-15 08:00:00',
            endsAt: '2026-09-15 17:00:00',
        ),
        studentPermitReadinessPayload(
            permitNo: 'IZN-PRESENSI-OUTSIDE-DATE',
            studentId: $approvedStudentId,
            studentNo: 'NIS-PRS-IZN-001',
            studentName: 'Aisyah Approved Presensi',
            status: 'approved',
            startsAt: '2026-09-16 08:00:00',
            endsAt: '2026-09-16 17:00:00',
        ),
    ]);

    return [
        'approved_student_id' => $approvedStudentId,
        'checked_out_student_id' => $checkedOutStudentId,
        'returned_student_id' => $returnedStudentId,
        'submitted_student_id' => $submittedStudentId,
        'void_student_id' => $voidStudentId,
    ];
}

/** @return array<string, mixed> */
function studentPermitReadinessPayload(
    string $permitNo,
    string $studentId,
    string $studentNo,
    string $studentName,
    string $status,
    string $startsAt,
    string $endsAt,
    ?string $returnedAt = null,
): array {
    return [
        'id' => (string) Str::ulid(),
        'permit_no' => $permitNo,
        'student_id' => $studentId,
        'student_no' => $studentNo,
        'student_name' => $studentName,
        'permit_type' => 'home_visit',
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'destination' => 'Rumah wali',
        'reason' => 'Izin untuk kebutuhan presensi.',
        'status' => $status,
        'submitted_at' => '2026-09-14 08:00:00',
        'reviewed_at' => '2026-09-14 10:00:00',
        'checked_out_at' => $status === 'checked_out' || $status === 'returned' ? '2026-09-15 08:15:00' : null,
        'returned_at' => $returnedAt,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

function seedDormitoryResidentReadinessData(): void
{
    DB::table('organization_units')->insert([
        'id' => '01K6ATTSNDRDY000000000020',
        'code' => 'ASP',
        'name' => 'Asrama Putri',
        'type' => 'dormitory',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('dormitories')->insert([
        'id' => '01K6ATTSNDRDY000000000023',
        'unit_id' => '01K6ATTSNDRDY000000000020',
        'code' => 'ASP-01',
        'name' => 'Asrama Putri 1',
        'gender_policy' => 'female',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('dormitory_rooms')->insert([
        'id' => '01K6ATTSNDRDY000000000024',
        'dormitory_id' => '01K6ATTSNDRDY000000000023',
        'code' => 'A-01',
        'name' => 'Kamar A 01',
        'capacity' => 4,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000026',
            'student_no' => 'NIS-PS-004',
            'full_name' => 'Dewi Asrama',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000027',
            'student_no' => 'NIS-PS-005',
            'full_name' => 'Eka Pindah',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_room_placements')->insert([
        [
            'id' => '01K6ATTSNDRDY000000000028',
            'student_id' => '01K6ATTSNDRDY000000000026',
            'dormitory_room_id' => '01K6ATTSNDRDY000000000024',
            'student_no' => 'NIS-PS-004',
            'started_at' => '2026-07-01 07:00:00',
            'ended_at' => null,
            'status' => 'active',
            'active_student_key' => '01K6ATTSNDRDY000000000026',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K6ATTSNDRDY000000000029',
            'student_id' => '01K6ATTSNDRDY000000000027',
            'dormitory_room_id' => '01K6ATTSNDRDY000000000024',
            'student_no' => 'NIS-PS-005',
            'started_at' => '2026-07-01 07:00:00',
            'ended_at' => '2026-08-01 07:00:00',
            'status' => 'moved',
            'active_student_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
