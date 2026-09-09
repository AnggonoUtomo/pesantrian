<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;

it('can read active students for achievement selectors through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01KPRSREADYUNIT000000001',
        'code' => 'PRS-MTS',
        'name' => 'MTs Prestasi',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01KPRSREADYSTUDENT001',
            'student_no' => 'NIS-PRS-001',
            'full_name' => 'Ahmad Juara',
            'primary_unit_id' => '01KPRSREADYUNIT000000001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPRSREADYSTUDENT002',
            'student_no' => 'NIS-PRS-002',
            'full_name' => 'Santri Nonaktif',
            'primary_unit_id' => '01KPRSREADYUNIT000000001',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveStudentReader::class)->options(
        primaryUnitId: '01KPRSREADYUNIT000000001',
        search: 'Juara',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KPRSREADYSTUDENT001')
        ->and($options[0]->studentNo)->toBe('NIS-PRS-001')
        ->and($options[0]->fullName)->toBe('Ahmad Juara')
        ->and(app(ActiveStudentReader::class)->findActive('01KPRSREADYSTUDENT002'))->toBeNull();
});

it('can read active mentors for achievement selectors through the HumanResource public contract', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01KPRSREADYEMPLOYEE001',
            'employee_no' => 'PEG-PRS-001',
            'name' => 'Ustadzah Pembina Prestasi',
            'employment_type' => 'teacher',
            'position' => 'Pembina Prestasi',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KPRSREADYEMPLOYEE002',
            'employee_no' => 'PEG-PRS-002',
            'name' => 'Pembina Nonaktif',
            'employment_type' => 'teacher',
            'position' => 'Pembina Prestasi',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveEmployeeReader::class)->options(
        employmentType: 'teacher',
        search: 'Pembina Prestasi',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01KPRSREADYEMPLOYEE001')
        ->and($options[0]->employeeNo)->toBe('PEG-PRS-001')
        ->and($options[0]->name)->toBe('Ustadzah Pembina Prestasi')
        ->and($options[0]->position)->toBe('Pembina Prestasi')
        ->and(app(ActiveEmployeeReader::class)->findActive('01KPRSREADYEMPLOYEE002'))->toBeNull();
});

it('can read the current active academic period for achievement recap context', function (): void {
    DB::table('academic_years')->insert([
        'id' => '01KPRSREADYACADYEAR001',
        'code' => '2026-2027-PRS',
        'name' => 'Tahun Ajaran Prestasi 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01KPRSREADYACADTERM001',
        'academic_year_id' => '01KPRSREADYACADYEAR001',
        'code' => 'PRS-2026-1',
        'name' => 'Semester Prestasi 2026/2027',
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
        ->and($period?->termId)->toBe('01KPRSREADYACADTERM001')
        ->and($period?->academicYearId)->toBe('01KPRSREADYACADYEAR001')
        ->and($period?->termName)->toBe('Semester Prestasi 2026/2027')
        ->and($period?->academicYearName)->toBe('Tahun Ajaran Prestasi 2026/2027');
});

it('does not import Infrastructure models from dependency modules', function (): void {
    $modulePath = base_path('app/Modules/Pesantrian/PrestasiSantri');
    $forbiddenImports = [
        'App\\Modules\\Academic\\AcademicPeriod\\Infrastructure\\',
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
                    'PrestasiSantri must use public contracts instead of importing %s in %s.',
                    $forbiddenImport,
                    $file->getPathname(),
                ));
        }
    }
});
