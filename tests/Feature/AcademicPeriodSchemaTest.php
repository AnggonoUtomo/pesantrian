<?php

use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use Illuminate\Support\Facades\Schema;

it('memiliki schema academic years dan academic terms dengan ULID dan field minimum', function (): void {
    expect(Schema::hasTable('academic_years'))->toBeTrue()
        ->and(Schema::hasColumns('academic_years', [
            'id',
            'code',
            'name',
            'starts_on',
            'ends_on',
            'status',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('academic_terms'))->toBeTrue()
        ->and(Schema::hasColumns('academic_terms', [
            'id',
            'academic_year_id',
            'code',
            'name',
            'sequence',
            'starts_on',
            'ends_on',
            'status',
            'is_active',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('academic_years', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('academic_terms', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('academic_terms', 'academic_year_id'))->toBeIn(['string', 'varchar']);
});

it('membuat academic year dan term record dengan ULID otomatis serta relasi minimum', function (): void {
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ]);

    $term = AcademicTermRecord::query()->create([
        'academic_year_id' => $year->getKey(),
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'draft',
        'is_active' => false,
    ]);

    expect((string) $year->getKey())->toHaveLength(26)
        ->and((string) $term->getKey())->toHaveLength(26)
        ->and($year->terms)->toHaveCount(1)
        ->and($term->academicYear->is($year))->toBeTrue()
        ->and($term->is_active)->toBeFalse()
        ->and($year->starts_on->toDateString())->toBe('2026-07-01')
        ->and($term->sequence)->toBe(1);

    $this->assertDatabaseHas('academic_years', [
        'id' => $year->getKey(),
        'code' => '2026-2027',
        'status' => 'draft',
    ]);
    $this->assertDatabaseHas('academic_terms', [
        'id' => $term->getKey(),
        'academic_year_id' => $year->getKey(),
        'code' => '2026-2027-GANJIL',
        'is_active' => false,
    ]);
});
