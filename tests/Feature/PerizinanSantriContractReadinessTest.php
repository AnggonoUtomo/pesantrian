<?php

declare(strict_types=1);

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\PrimaryStudentGuardianReader;
use Illuminate\Support\Facades\DB;

it('can read active students for permit candidates through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01KPERMITRDYUNIT000000001',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01KPERMITRDYSTUDENT0001',
            'student_no' => 'NIS-IZN-001',
            'full_name' => 'Ahmad Izin',
            'primary_unit_id' => '01KPERMITRDYUNIT000000001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPERMITRDYSTUDENT0002',
            'student_no' => 'NIS-IZN-002',
            'full_name' => 'Santri Nonaktif',
            'primary_unit_id' => '01KPERMITRDYUNIT000000001',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveStudentReader::class)->options(
        primaryUnitId: '01KPERMITRDYUNIT000000001',
        search: 'Ahmad',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KPERMITRDYSTUDENT0001')
        ->and($options[0]->studentNo)->toBe('NIS-IZN-001')
        ->and($options[0]->fullName)->toBe('Ahmad Izin')
        ->and(app(ActiveStudentReader::class)->findActive('01KPERMITRDYSTUDENT0002'))->toBeNull();
});

it('can read active employees for permit officers through the HumanResource public contract', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01KPERMITRDYEMPLOYEE001',
            'employee_no' => 'PEG-IZN-001',
            'name' => 'Ustadzah Fatimah',
            'employment_type' => 'staff',
            'position' => 'Petugas Perizinan',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPERMITRDYEMPLOYEE002',
            'employee_no' => 'PEG-IZN-002',
            'name' => 'Pegawai Nonaktif',
            'employment_type' => 'staff',
            'position' => 'Petugas Perizinan',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveEmployeeReader::class)->options(
        employmentType: 'staff',
        search: 'Fatimah',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KPERMITRDYEMPLOYEE001')
        ->and($options[0]->employeeNo)->toBe('PEG-IZN-001')
        ->and($options[0]->name)->toBe('Ustadzah Fatimah')
        ->and(app(ActiveEmployeeReader::class)->findActive('01KPERMITRDYEMPLOYEE002'))->toBeNull();
});

it('can read primary guardian snapshot for permit forms through the Santri public contract', function (): void {
    DB::table('students')->insert([
        [
            'id' => '01KPERMITRDYSTUDENT0003',
            'student_no' => 'NIS-IZN-003',
            'full_name' => 'Budi Izin',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPERMITRDYSTUDENT0004',
            'student_no' => 'NIS-IZN-004',
            'full_name' => 'Cici Alumni',
            'status' => 'alumni',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::table('student_guardians')->insert([
        [
            'id' => '01KPERMITRDYGUARDIAN001',
            'student_id' => '01KPERMITRDYSTUDENT0003',
            'guardian_name' => 'Siti Aminah',
            'guardian_phone' => '081234567890',
            'guardian_relation' => 'ibu',
            'is_primary' => true,
            'is_emergency_contact' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPERMITRDYGUARDIAN002',
            'student_id' => '01KPERMITRDYSTUDENT0004',
            'guardian_name' => 'Wali Alumni',
            'guardian_phone' => '081200000000',
            'guardian_relation' => 'ayah',
            'is_primary' => true,
            'is_emergency_contact' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $guardian = app(PrimaryStudentGuardianReader::class)->primaryForActiveStudent('01KPERMITRDYSTUDENT0003');

    expect($guardian)->not->toBeNull()
        ->and($guardian?->studentId)->toBe('01KPERMITRDYSTUDENT0003')
        ->and($guardian?->guardianName)->toBe('Siti Aminah')
        ->and($guardian?->guardianPhone)->toBe('081234567890')
        ->and($guardian?->guardianRelation)->toBe('ibu')
        ->and(app(PrimaryStudentGuardianReader::class)->primaryForActiveStudent('01KPERMITRDYSTUDENT0004'))->toBeNull();
});

it('does not import Infrastructure models from dependency modules', function (): void {
    $modulePath = base_path('app/Modules/Pesantrian/PerizinanSantri');
    $forbiddenImports = [
        'App\\Modules\\HumanResource\\HumanResource\\Infrastructure\\',
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
                    'PerizinanSantri must use public contracts instead of importing %s in %s.',
                    $forbiddenImport,
                    $file->getPathname(),
                ));
        }
    }
});
