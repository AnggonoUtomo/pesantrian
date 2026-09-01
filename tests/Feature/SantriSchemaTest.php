<?php

use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('memiliki schema students dan student guardians sesuai data foundation Santri', function (): void {
    expect(Schema::hasTable('students'))->toBeTrue()
        ->and(Schema::hasColumns('students', [
            'id',
            'student_no',
            'admission_id',
            'registration_no',
            'full_name',
            'preferred_name',
            'gender',
            'birth_place',
            'birth_date',
            'previous_school',
            'primary_unit_id',
            'entry_date',
            'status',
            'status_reason',
            'status_changed_at',
            'status_changed_by',
            'archived_at',
            'archived_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_guardians'))->toBeTrue()
        ->and(Schema::hasColumns('student_guardians', [
            'id',
            'student_id',
            'guardian_name',
            'guardian_phone',
            'guardian_relation',
            'is_primary',
            'is_emergency_contact',
            'notes',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('students', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('students', 'primary_unit_id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_guardians', 'student_id'))->toBeIn(['string', 'varchar']);
});

it('membuat student record dengan ULID otomatis dan casts minimum', function (): void {
    $unitId = (string) Str::ulid();

    DB::table('organization_units')->insert([
        'id' => $unitId,
        'parent_id' => null,
        'code' => 'MTS-SANTRI',
        'name' => 'Madrasah Tsanawiyah Santri',
        'type' => 'education_unit',
        'status' => 'active',
        'location_name' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $student = StudentRecord::query()->create([
        'student_no' => 'NIS-0001',
        'admission_id' => (string) Str::ulid(),
        'registration_no' => 'SNTR-0001',
        'full_name' => 'Muhammad Fikri',
        'preferred_name' => 'Fikri',
        'gender' => 'male',
        'birth_place' => 'Bandung',
        'birth_date' => '2013-05-10',
        'previous_school' => 'SD Negeri 1',
        'primary_unit_id' => $unitId,
        'entry_date' => '2026-07-15',
        'status' => 'active',
    ]);

    expect((string) $student->getKey())->toHaveLength(26)
        ->and($student->student_no)->toBe('NIS-0001')
        ->and($student->birth_date->toDateString())->toBe('2013-05-10')
        ->and($student->entry_date->toDateString())->toBe('2026-07-15')
        ->and($student->status)->toBe('active');

    $exists = DB::table('students')
        ->where('id', $student->getKey())
        ->where('student_no', 'NIS-0001')
        ->where('full_name', 'Muhammad Fikri')
        ->where('primary_unit_id', $unitId)
        ->exists();

    expect($exists)->toBeTrue();
});

it('membuat snapshot wali minimum yang terhubung ke student', function (): void {
    $student = StudentRecord::factory()->create([
        'student_no' => 'NIS-0002',
        'full_name' => 'Aisyah Zahra',
    ]);

    $guardian = StudentGuardianRecord::query()->create([
        'student_id' => $student->getKey(),
        'guardian_name' => 'Ahmad Fadli',
        'guardian_phone' => '081234567890',
        'guardian_relation' => 'ayah',
        'is_primary' => true,
        'is_emergency_contact' => true,
        'notes' => null,
    ]);

    expect((string) $guardian->getKey())->toHaveLength(26)
        ->and($guardian->student_id)->toBe($student->getKey())
        ->and($guardian->is_primary)->toBeTrue()
        ->and($guardian->is_emergency_contact)->toBeTrue()
        ->and($student->guardians)->toHaveCount(1)
        ->and($student->guardians->first()->guardian_name)->toBe('Ahmad Fadli');
});
