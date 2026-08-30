<?php

use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

it('memiliki schema student admissions dengan ULID dan field minimum PPDB', function (): void {
    expect(Schema::hasTable('student_admissions'))->toBeTrue()
        ->and(Schema::hasColumns('student_admissions', [
            'id',
            'registration_no',
            'registration_period',
            'candidate_name',
            'candidate_gender',
            'candidate_birth_place',
            'candidate_birth_date',
            'previous_school',
            'target_unit_id',
            'guardian_name',
            'guardian_phone',
            'guardian_relation',
            'registration_fee_required',
            'registration_fee_amount',
            'registration_fee_status',
            'document_checklist',
            'status',
            'registered_at',
            'decided_at',
            'decided_by',
            'notes',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('student_admissions', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_admissions', 'target_unit_id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_admissions', 'decided_by'))->toBeIn(['string', 'varchar']);
});

it('membuat student admission record dengan ULID otomatis dan casts minimum', function (): void {
    $unitId = (string) Str::ulid();
    DB::table('organization_units')->insert([
        'id' => $unitId,
        'parent_id' => null,
        'code' => 'MTS',
        'name' => 'Madrasah Tsanawiyah',
        'type' => 'education_unit',
        'status' => 'active',
        'location_name' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admission = StudentAdmissionRecord::query()->create([
        'registration_no' => 'SNTR-0001',
        'registration_period' => 'PPDB 2027',
        'candidate_name' => 'Muhammad Fikri',
        'candidate_gender' => 'male',
        'candidate_birth_place' => 'Bandung',
        'candidate_birth_date' => '2013-05-10',
        'previous_school' => 'SD Negeri 1',
        'target_unit_id' => $unitId,
        'guardian_name' => 'Ahmad Fadli',
        'guardian_phone' => '081234567890',
        'guardian_relation' => 'ayah',
        'registration_fee_required' => true,
        'registration_fee_amount' => 250000,
        'registration_fee_status' => 'pending',
        'document_checklist' => [
            ['type' => 'kartu_keluarga', 'status' => 'submitted'],
        ],
        'status' => 'draft',
        'registered_at' => '2026-08-30 08:00:00',
        'notes' => null,
    ]);

    expect((string) $admission->getKey())->toHaveLength(26)
        ->and($admission->registration_no)->toBe('SNTR-0001')
        ->and($admission->candidate_birth_date->toDateString())->toBe('2013-05-10')
        ->and($admission->registration_fee_required)->toBeTrue()
        ->and($admission->registration_fee_amount)->toBe('250000.00')
        ->and($admission->document_checklist)->toBe([
            ['type' => 'kartu_keluarga', 'status' => 'submitted'],
        ]);

    $exists = DB::table('student_admissions')
        ->where('id', $admission->getKey())
        ->where('registration_no', 'SNTR-0001')
        ->where('candidate_name', 'Muhammad Fikri')
        ->where('target_unit_id', $unitId)
        ->where('registration_fee_status', 'pending')
        ->where('status', 'draft')
        ->exists();

    expect($exists)->toBeTrue();
});
