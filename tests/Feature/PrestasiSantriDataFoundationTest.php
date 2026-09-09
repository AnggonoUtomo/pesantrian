<?php

declare(strict_types=1);

use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRevisionRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the PrestasiSantri foundation tables with expected columns', function (): void {
    expect(Schema::hasTable('student_achievement_categories'))->toBeTrue()
        ->and(Schema::hasColumns('student_achievement_categories', [
            'id',
            'code',
            'name',
            'description',
            'status',
            'archived_at',
            'archived_by',
            'archive_reason',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_achievements'))->toBeTrue()
        ->and(Schema::hasColumns('student_achievements', [
            'id',
            'achievement_no',
            'category_id',
            'category_name',
            'student_id',
            'student_no',
            'student_name',
            'academic_period_id',
            'academic_period_label',
            'mentor_employee_id',
            'mentor_name',
            'title',
            'achievement_type',
            'level',
            'result',
            'organizer',
            'event_name',
            'event_location',
            'achieved_on',
            'period_started_on',
            'period_ended_on',
            'description',
            'notes',
            'status',
            'submitted_at',
            'submitted_by',
            'verified_at',
            'verified_by',
            'verification_note',
            'voided_at',
            'voided_by',
            'void_reason',
            'created_by',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('student_achievement_revisions'))->toBeTrue()
        ->and(Schema::hasColumns('student_achievement_revisions', [
            'id',
            'achievement_id',
            'from_status',
            'to_status',
            'reason',
            'changed_by',
            'changed_at',
            'summary',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('student_achievement_categories', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_achievements', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('student_achievement_revisions', 'id'))->toBeIn(['string', 'varchar']);
});

it('guards duplicate category codes and achievement numbers', function (): void {
    seedPrestasiSantriFoundationReferences();
    seedPrestasiSantriFoundationCategory();
    seedPrestasiSantriFoundationAchievement();

    expect(fn () => DB::table('student_achievement_categories')->insert([
        'id' => '01KACHIEVEMENTDATA00000004',
        'code' => 'AKADEMIK',
        'name' => 'Akademik Duplikat',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class)
        ->and(fn () => DB::table('student_achievements')->insert([
            'id' => '01KACHIEVEMENTDATA00000005',
            'achievement_no' => 'PRS-000001',
            'category_id' => '01KACHIEVEMENTDATA00000001',
            'category_name' => 'Akademik',
            'student_id' => '01KACHIEVEMENTDATA00000010',
            'student_no' => 'NIS-PRS-101',
            'student_name' => 'Ahmad Juara',
            'title' => 'Juara Olimpiade Matematika',
            'achievement_type' => 'competition',
            'level' => 'district',
            'result' => 'Juara 1',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
});

it('stores structured snapshots for achievement history', function (): void {
    seedPrestasiSantriFoundationReferences();
    seedPrestasiSantriFoundationCategory();

    DB::table('student_achievements')->insert([
        'id' => '01KACHIEVEMENTDATA00000002',
        'achievement_no' => 'PRS-000001',
        'category_id' => '01KACHIEVEMENTDATA00000001',
        'category_name' => 'Akademik',
        'student_id' => '01KACHIEVEMENTDATA00000010',
        'student_no' => 'NIS-PRS-101',
        'student_name' => 'Ahmad Juara',
        'academic_period_id' => '01KACHIEVEMENTDATA00000030',
        'academic_period_label' => 'Semester Ganjil 2026/2027',
        'mentor_employee_id' => '01KACHIEVEMENTDATA00000040',
        'mentor_name' => 'Ustadzah Pembina Prestasi',
        'title' => 'Juara Olimpiade Matematika',
        'achievement_type' => 'competition',
        'level' => 'district',
        'result' => 'Juara 1',
        'organizer' => 'Kemenag Kabupaten',
        'event_name' => 'Olimpiade Matematika Santri',
        'event_location' => 'Kota Saka',
        'achieved_on' => '2026-09-17',
        'description' => 'Santri meraih juara pada lomba matematika tingkat kabupaten.',
        'notes' => 'Perlu disiapkan untuk lomba provinsi.',
        'status' => 'draft',
        'created_by' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->assertDatabaseHas('student_achievements', [
        'id' => '01KACHIEVEMENTDATA00000002',
        'student_no' => 'NIS-PRS-101',
        'student_name' => 'Ahmad Juara',
        'category_name' => 'Akademik',
        'academic_period_label' => 'Semester Ganjil 2026/2027',
        'mentor_name' => 'Ustadzah Pembina Prestasi',
        'result' => 'Juara 1',
    ]);
});

it('keeps revision history rows for the same achievement', function (): void {
    seedPrestasiSantriFoundationReferences();
    seedPrestasiSantriFoundationCategory();
    seedPrestasiSantriFoundationAchievement();

    DB::table('student_achievement_revisions')->insert([
        [
            'id' => '01KACHIEVEMENTDATA00000006',
            'achievement_id' => '01KACHIEVEMENTDATA00000002',
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'reason' => 'Catatan prestasi diajukan untuk verifikasi.',
            'changed_by' => null,
            'changed_at' => '2026-09-17 08:00:00',
            'summary' => json_encode(['changed_fields' => ['status']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01KACHIEVEMENTDATA00000007',
            'achievement_id' => '01KACHIEVEMENTDATA00000002',
            'from_status' => 'submitted',
            'to_status' => 'verified',
            'reason' => 'Catatan prestasi sudah sesuai bukti kegiatan.',
            'changed_by' => null,
            'changed_at' => '2026-09-17 09:00:00',
            'summary' => json_encode(['changed_fields' => ['status', 'verification_note']], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(DB::table('student_achievement_revisions')->where('achievement_id', '01KACHIEVEMENTDATA00000002')->count())->toBe(2);
});

it('creates achievement records through local factories', function (): void {
    seedPrestasiSantriFoundationReferences();

    $category = StudentAchievementCategoryRecord::factory()->create([
        'code' => 'FACTORY',
        'name' => 'Kategori Factory',
    ]);
    $achievement = StudentAchievementRecord::factory()->create([
        'category_id' => $category->id,
        'category_name' => $category->name,
        'student_id' => '01KACHIEVEMENTDATA00000010',
        'student_no' => 'NIS-PRS-101',
        'student_name' => 'Ahmad Juara',
        'academic_period_id' => '01KACHIEVEMENTDATA00000030',
        'academic_period_label' => 'Semester Ganjil 2026/2027',
        'mentor_employee_id' => '01KACHIEVEMENTDATA00000040',
        'mentor_name' => 'Ustadzah Pembina Prestasi',
    ]);
    $revision = StudentAchievementRevisionRecord::factory()->create([
        'achievement_id' => $achievement->id,
    ]);

    expect($category->getKey())->toBeString()
        ->and($achievement->category->is($category))->toBeTrue()
        ->and($achievement->revisions)->toHaveCount(1)
        ->and($revision->achievement->is($achievement))->toBeTrue()
        ->and($revision->summary)->toBe(['changed_fields' => ['status']]);
});

function seedPrestasiSantriFoundationReferences(): void
{
    DB::table('organization_units')->insert([
        'id' => '01KACHIEVEMENTDATA00000020',
        'code' => 'MTP',
        'name' => 'MTs Prestasi',
        'type' => 'education_unit',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('students')->insert([
        'id' => '01KACHIEVEMENTDATA00000010',
        'student_no' => 'NIS-PRS-101',
        'full_name' => 'Ahmad Juara',
        'primary_unit_id' => '01KACHIEVEMENTDATA00000020',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_years')->insert([
        'id' => '01KACHIEVEMENTDATA00000031',
        'code' => '2026-2027-PRS',
        'name' => 'Tahun Ajaran Prestasi 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('academic_terms')->insert([
        'id' => '01KACHIEVEMENTDATA00000030',
        'academic_year_id' => '01KACHIEVEMENTDATA00000031',
        'code' => 'PRS-2026-1',
        'name' => 'Semester Ganjil 2026/2027',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('employees')->insert([
        'id' => '01KACHIEVEMENTDATA00000040',
        'employee_no' => 'PEG-PRS-101',
        'name' => 'Ustadzah Pembina Prestasi',
        'employment_type' => 'teacher',
        'position' => 'Pembina Prestasi',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedPrestasiSantriFoundationCategory(): void
{
    DB::table('student_achievement_categories')->insert([
        'id' => '01KACHIEVEMENTDATA00000001',
        'code' => 'AKADEMIK',
        'name' => 'Akademik',
        'description' => 'Prestasi akademik dan lomba pelajaran.',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function seedPrestasiSantriFoundationAchievement(): void
{
    DB::table('student_achievements')->insert([
        'id' => '01KACHIEVEMENTDATA00000002',
        'achievement_no' => 'PRS-000001',
        'category_id' => '01KACHIEVEMENTDATA00000001',
        'category_name' => 'Akademik',
        'student_id' => '01KACHIEVEMENTDATA00000010',
        'student_no' => 'NIS-PRS-101',
        'student_name' => 'Ahmad Juara',
        'title' => 'Juara Olimpiade Matematika',
        'achievement_type' => 'competition',
        'level' => 'district',
        'result' => 'Juara 1',
        'status' => 'draft',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
