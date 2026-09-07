<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;

it('can read active students for Tahfidz selectors through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01K7THFDZ0SANTRIUNIT0001',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K7THFDZ0SANTRIACT001',
            'student_no' => 'NIS-TAH-001',
            'full_name' => 'Ahmad Hafidz',
            'primary_unit_id' => '01K7THFDZ0SANTRIUNIT0001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K7THFDZ0SANTRIINA001',
            'student_no' => 'NIS-TAH-002',
            'full_name' => 'Santri Nonaktif',
            'primary_unit_id' => '01K7THFDZ0SANTRIUNIT0001',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveStudentReader::class)->options(
        primaryUnitId: '01K7THFDZ0SANTRIUNIT0001',
        search: 'Ahmad',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01K7THFDZ0SANTRIACT001')
        ->and($options[0]->studentNo)->toBe('NIS-TAH-001')
        ->and($options[0]->fullName)->toBe('Ahmad Hafidz')
        ->and(app(ActiveStudentReader::class)->findActive('01K7THFDZ0SANTRIINA001'))->toBeNull();
});

it('can read active supervisors for Tahfidz selectors through the HumanResource public contract', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01K7THFDZ0USTADZACT001',
            'employee_no' => 'PEG-TAH-001',
            'name' => 'Ustadz Hasan Tahfidz',
            'employment_type' => 'teacher',
            'position' => 'Pembimbing Tahfidz',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K7THFDZ0USTADZINA001',
            'employee_no' => 'PEG-TAH-002',
            'name' => 'Ustadz Nonaktif',
            'employment_type' => 'teacher',
            'position' => 'Pembimbing Tahfidz',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveEmployeeReader::class)->options(
        employmentType: 'teacher',
        search: 'Hasan',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01K7THFDZ0USTADZACT001')
        ->and($options[0]->employeeNo)->toBe('PEG-TAH-001')
        ->and($options[0]->name)->toBe('Ustadz Hasan Tahfidz')
        ->and(app(ActiveEmployeeReader::class)->findActive('01K7THFDZ0USTADZINA001'))->toBeNull();
});

it('can read the current active academic period for Tahfidz target context', function (): void {
    DB::table('academic_years')->insert([
        'id' => '01K7THFDZ0ACADYEAR0001',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K7THFDZ0ACADTERM0001',
        'academic_year_id' => '01K7THFDZ0ACADYEAR0001',
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

    $period = app(ActiveAcademicPeriodReader::class)->current();

    expect($period)->not->toBeNull()
        ->and($period?->termId)->toBe('01K7THFDZ0ACADTERM0001')
        ->and($period?->academicYearId)->toBe('01K7THFDZ0ACADYEAR0001')
        ->and($period?->termName)->toBe('Semester Ganjil 2026/2027')
        ->and($period?->academicYearName)->toBe('Tahun Ajaran 2026/2027');
});

it('does not import Infrastructure models from dependency modules', function (): void {
    $modulePath = base_path('app/Modules/Pesantrian/Tahfidz');
    $forbiddenImports = [
        'App\\Modules\\Academic\\AcademicPeriod\\Infrastructure\\',
        'App\\Modules\\Academic\\KelasRombel\\Infrastructure\\',
        'App\\Modules\\HumanResource\\HumanResource\\Infrastructure\\',
        'App\\Modules\\Pesantrian\\Asrama\\Infrastructure\\',
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
                    'Tahfidz must use public contracts instead of importing %s in %s.',
                    $forbiddenImport,
                    $file->getPathname(),
                ));
        }
    }
});
