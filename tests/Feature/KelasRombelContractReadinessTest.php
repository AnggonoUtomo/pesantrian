<?php

declare(strict_types=1);

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Organization\Organization\Application\Contracts\EducationUnitReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;

it('exposes education unit options through the Organization public contract', function (): void {
    DB::table('organization_units')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T31AA1',
            'code' => 'MTS',
            'name' => 'MTs Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T31AA2',
            'code' => 'ASP',
            'name' => 'Asrama Putra',
            'type' => 'boarding_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(EducationUnitReader::class)->options();

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01K41KRG60H6GTYB56B6T31AA1')
        ->and($options[0]->code)->toBe('MTS')
        ->and($options[0]->name)->toBe('MTs Saka');
});

it('exposes the current active academic period through the AcademicPeriod public contract', function (): void {
    DB::table('academic_years')->insert([
        'id' => '01K41KRG60H6GTYB56B6T31AB1',
        'code' => '2026-2027',
        'name' => 'Tahun Ajaran 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01K41KRG60H6GTYB56B6T31AB2',
        'academic_year_id' => '01K41KRG60H6GTYB56B6T31AB1',
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
        ->and($period->termId)->toBe('01K41KRG60H6GTYB56B6T31AB2')
        ->and($period->academicYearId)->toBe('01K41KRG60H6GTYB56B6T31AB1')
        ->and($period->termName)->toBe('Semester Ganjil 2026/2027')
        ->and($period->academicYearName)->toBe('Tahun Ajaran 2026/2027');
});

it('exposes active student options through the Santri public contract', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01K41KRG60H6GTYB56B6T31AC1',
        'code' => 'MA',
        'name' => 'MA Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T31AC2',
            'student_no' => 'NIS-0001',
            'full_name' => 'Ahmad Fikri',
            'primary_unit_id' => '01K41KRG60H6GTYB56B6T31AC1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T31AC3',
            'student_no' => 'NIS-0002',
            'full_name' => 'Budi Santoso',
            'primary_unit_id' => '01K41KRG60H6GTYB56B6T31AC1',
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(ActiveStudentReader::class)->options(
        primaryUnitId: '01K41KRG60H6GTYB56B6T31AC1',
        search: 'Ahmad',
    );

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01K41KRG60H6GTYB56B6T31AC2')
        ->and($options[0]->studentNo)->toBe('NIS-0001')
        ->and($options[0]->fullName)->toBe('Ahmad Fikri');

    $activeStudent = app(ActiveStudentReader::class)->findActive(
        studentId: '01K41KRG60H6GTYB56B6T31AC2',
        primaryUnitId: '01K41KRG60H6GTYB56B6T31AC1',
    );

    expect($activeStudent)->not->toBeNull()
        ->and($activeStudent?->studentNo)->toBe('NIS-0001')
        ->and(app(ActiveStudentReader::class)->findActive('01K41KRG60H6GTYB56B6T31AC3'))->toBeNull();
});

it('exposes active employee options through the HumanResource public contract', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T31AD1',
            'employee_no' => 'PEG-0001',
            'name' => 'Ustadz Hasan',
            'employment_type' => 'teacher',
            'position' => 'Wali Kelas',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T31AD2',
            'employee_no' => 'PEG-0002',
            'name' => 'Ustadz Nonaktif',
            'employment_type' => 'teacher',
            'position' => 'Wali Kelas',
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
        ->and($options[0]->id)->toBe('01K41KRG60H6GTYB56B6T31AD1')
        ->and($options[0]->employeeNo)->toBe('PEG-0001')
        ->and($options[0]->name)->toBe('Ustadz Hasan');
});
