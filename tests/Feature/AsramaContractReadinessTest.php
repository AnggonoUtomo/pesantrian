<?php

declare(strict_types=1);

use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Organization\Organization\Application\Contracts\DormitoryUnitReader;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Support\Facades\DB;

it('exposes dormitory unit options through the Organization public contract', function (): void {
    DB::table('organization_units')->insert([
        [
            'id' => '01K5MB2G88B4P2FM4Q9V8A0AA1',
            'code' => 'ASP',
            'name' => 'Asrama Putra',
            'type' => 'dormitory',
            'status' => 'active',
            'location_name' => 'Blok Putra',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K5MB2G88B4P2FM4Q9V8A0AA2',
            'code' => 'MTS',
            'name' => 'MTs Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'location_name' => 'Gedung MTs',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K5MB2G88B4P2FM4Q9V8A0AA3',
            'code' => 'ASL',
            'name' => 'Asrama Lama',
            'type' => 'dormitory',
            'status' => 'inactive',
            'location_name' => 'Blok Lama',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $options = app(DormitoryUnitReader::class)->options(search: 'Putra');

    expect($options)->toHaveCount(1)
        ->and($options[0]->id)->toBe('01K5MB2G88B4P2FM4Q9V8A0AA1')
        ->and($options[0]->code)->toBe('ASP')
        ->and($options[0]->name)->toBe('Asrama Putra')
        ->and($options[0]->locationName)->toBe('Blok Putra');
});

it('exposes active student gender for Asrama placement rules', function (): void {
    DB::table('organization_units')->insert([
        'id' => '01K5MB2G88B4P2FM4Q9V8A0AB1',
        'code' => 'MTS',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        [
            'id' => '01K5MB2G88B4P2FM4Q9V8A0AB2',
            'student_no' => 'NIS-ASR-001',
            'full_name' => 'Ahmad Asrama',
            'gender' => 'male',
            'primary_unit_id' => '01K5MB2G88B4P2FM4Q9V8A0AB1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $student = app(ActiveStudentReader::class)->findActive('01K5MB2G88B4P2FM4Q9V8A0AB2');

    expect($student)->not->toBeNull()
        ->and($student?->studentNo)->toBe('NIS-ASR-001')
        ->and($student?->gender)->toBe('male');
});

it('reuses active employee contract for musyrif selection', function (): void {
    DB::table('employees')->insert([
        [
            'id' => '01K5MB2G88B4P2FM4Q9V8A0AC1',
            'employee_no' => 'PEG-ASR-001',
            'name' => 'Ustadz Musyrif',
            'employment_type' => 'teacher',
            'position' => 'Musyrif Asrama',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $employees = app(ActiveEmployeeReader::class)->options(search: 'Musyrif');

    expect($employees)->toHaveCount(1)
        ->and($employees[0]->id)->toBe('01K5MB2G88B4P2FM4Q9V8A0AC1')
        ->and($employees[0]->position)->toBe('Musyrif Asrama');
});
