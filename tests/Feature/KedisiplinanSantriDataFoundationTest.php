<?php

declare(strict_types=1);

use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the KedisiplinanSantri foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('student_discipline_categories'))->toBeTrue()
        ->and(Schema::hasColumns('student_discipline_categories', [
            'id',
            'code',
            'name',
            'description',
            'default_severity',
            'default_points',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_discipline_cases'))->toBeTrue()
        ->and(Schema::hasColumns('student_discipline_cases', [
            'id',
            'case_no',
            'student_id',
            'student_no',
            'student_name',
            'unit_id',
            'unit_name',
            'category_id',
            'category_name',
            'severity',
            'points',
            'occurred_at',
            'location',
            'description',
            'reported_by',
            'assigned_employee_id',
            'assigned_employee_name',
            'status',
            'submitted_at',
            'reviewed_at',
            'reviewed_by',
            'review_note',
            'action_plan',
            'action_assigned_at',
            'resolved_at',
            'resolved_by',
            'resolution_note',
            'voided_at',
            'voided_by',
            'void_reason',
            'created_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_discipline_revisions'))->toBeTrue()
        ->and(Schema::hasColumns('student_discipline_revisions', [
            'id',
            'case_id',
            'reason',
            'changed_by',
            'changed_at',
            'summary',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('student_discipline_categories', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_discipline_cases', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_discipline_revisions', 'id'))->toBeIn(['string', 'varchar']);
});

it('guards duplicate category codes and case numbers', function (): void {
    seedKedisiplinanFoundationReferences();
    seedKedisiplinanFoundationCategory();
    seedKedisiplinanFoundationCase();

    expect(fn () => DB::table('student_discipline_categories')->insert([
        'id' => '01KDISCIPLINEDATA000000004',
        'code' => 'TERLAMBAT',
        'name' => 'Terlambat Duplikat',
        'default_severity' => 'minor',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('student_discipline_cases')->insert([
            'id' => '01KDISCIPLINEDATA000000005',
            'case_no' => 'DIS-000001',
            'student_id' => '01KDISCIPLINEDATA000000010',
            'student_no' => 'NIS-DIS-101',
            'student_name' => 'Ahmad Disiplin',
            'category_id' => '01KDISCIPLINEDATA000000001',
            'category_name' => 'Terlambat kegiatan',
            'severity' => 'minor',
            'occurred_at' => '2026-09-16 07:30:00',
            'description' => 'Terlambat mengikuti kegiatan pagi.',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
});

it('stores structured snapshots for discipline case history', function (): void {
    seedKedisiplinanFoundationReferences();
    seedKedisiplinanFoundationCategory();

    DB::table('student_discipline_cases')->insert([
        'id' => '01KDISCIPLINEDATA000000002',
        'case_no' => 'DIS-000001',
        'student_id' => '01KDISCIPLINEDATA000000010',
        'student_no' => 'NIS-DIS-101',
        'student_name' => 'Ahmad Disiplin',
        'unit_id' => '01KDISCIPLINEDATA000000020',
        'unit_name' => 'MTs Saka',
        'category_id' => '01KDISCIPLINEDATA000000001',
        'category_name' => 'Terlambat kegiatan',
        'severity' => 'minor',
        'points' => 5,
        'occurred_at' => '2026-09-16 07:30:00',
        'location' => 'Masjid',
        'description' => 'Terlambat mengikuti kegiatan pagi.',
        'reported_by' => null,
        'assigned_employee_id' => '01KDISCIPLINEDATA000000040',
        'assigned_employee_name' => 'Ustadz Karim Pembina',
        'status' => 'draft',
        'created_by' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->assertDatabaseHas('student_discipline_cases', [
        'id' => '01KDISCIPLINEDATA000000002',
        'student_no' => 'NIS-DIS-101',
        'student_name' => 'Ahmad Disiplin',
        'unit_name' => 'MTs Saka',
        'category_name' => 'Terlambat kegiatan',
        'assigned_employee_name' => 'Ustadz Karim Pembina',
        'points' => 5,
    ]);
});

it('keeps revision history rows for the same discipline case', function (): void {
    seedKedisiplinanFoundationReferences();
    seedKedisiplinanFoundationCategory();
    seedKedisiplinanFoundationCase();

    DB::table('student_discipline_revisions')->insert([
        [
            'id' => '01KDISCIPLINEDATA000000006',
            'case_id' => '01KDISCIPLINEDATA000000002',
            'reason' => 'Koreksi kronologi kejadian.',
            'changed_by' => null,
            'changed_at' => '2026-09-16 08:00:00',
            'summary' => json_encode(['changed_fields' => ['description']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KDISCIPLINEDATA000000007',
            'case_id' => '01KDISCIPLINEDATA000000002',
            'reason' => 'Menambahkan tindakan pembinaan.',
            'changed_by' => null,
            'changed_at' => '2026-09-16 09:00:00',
            'summary' => json_encode(['changed_fields' => ['action_plan']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('student_discipline_revisions')->where('case_id', '01KDISCIPLINEDATA000000002')->count())->toBe(2);
});

it('creates discipline records through local factories', function (): void {
    seedKedisiplinanFoundationReferences();

    $category = StudentDisciplineCategoryRecord::factory()->create([
        'code' => 'FACTORY',
        'name' => 'Kategori Factory',
    ]);
    $case = StudentDisciplineCaseRecord::factory()->create([
        'category_id' => $category->id,
        'category_name' => $category->name,
        'student_id' => '01KDISCIPLINEDATA000000010',
        'student_no' => 'NIS-DIS-101',
        'student_name' => 'Ahmad Disiplin',
        'assigned_employee_id' => '01KDISCIPLINEDATA000000040',
        'assigned_employee_name' => 'Ustadz Karim Pembina',
    ]);
    $revision = StudentDisciplineRevisionRecord::factory()->create([
        'case_id' => $case->id,
    ]);

    expect($category->getKey())->toBeString()
        ->and($case->category->is($category))->toBeTrue()
        ->and($case->revisions)->toHaveCount(1)
        ->and($revision->case->is($case))->toBeTrue()
        ->and($revision->summary)->toBe(['changed_fields' => ['status']]);
});

function seedKedisiplinanFoundationReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01KDISCIPLINEDATA000000020',
        'code' => 'MTD',
        'name' => 'MTs Saka',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01KDISCIPLINEDATA000000010',
        'student_no' => 'NIS-DIS-101',
        'full_name' => 'Ahmad Disiplin',
        'primary_unit_id' => '01KDISCIPLINEDATA000000020',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        'id' => '01KDISCIPLINEDATA000000040',
        'employee_no' => 'PEG-DIS-101',
        'name' => 'Ustadz Karim Pembina',
        'employment_type' => 'staff',
        'position' => 'Pembina Kedisiplinan',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedKedisiplinanFoundationCategory(): void
{
    DB::table('student_discipline_categories')->insert([
        'id' => '01KDISCIPLINEDATA000000001',
        'code' => 'TERLAMBAT',
        'name' => 'Terlambat kegiatan',
        'default_severity' => 'minor',
        'default_points' => 5,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedKedisiplinanFoundationCase(): void
{
    DB::table('student_discipline_cases')->insert([
        'id' => '01KDISCIPLINEDATA000000002',
        'case_no' => 'DIS-000001',
        'student_id' => '01KDISCIPLINEDATA000000010',
        'student_no' => 'NIS-DIS-101',
        'student_name' => 'Ahmad Disiplin',
        'category_id' => '01KDISCIPLINEDATA000000001',
        'category_name' => 'Terlambat kegiatan',
        'severity' => 'minor',
        'occurred_at' => '2026-09-16 07:30:00',
        'description' => 'Terlambat mengikuti kegiatan pagi.',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
