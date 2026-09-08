<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListDisciplineOfficerCandidates;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListDisciplineStudentCandidates;
use Illuminate\Support\Facades\DB;

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
