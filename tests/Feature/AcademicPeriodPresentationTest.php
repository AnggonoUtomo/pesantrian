<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Inertia\Testing\AssertableInertia as Assert;

it('menolak actor tanpa permission academic period view', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('academic.periods.index'))
        ->assertForbidden();
});

it('menampilkan halaman Inertia periode akademik dari canonical frontend module', function (): void {
    $view = Permission::create(['name' => 'academic_period.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
    ]);
    AcademicTermRecord::query()->create([
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'active',
        'is_active' => true,
    ]);

    $this->actingAs($actor)
        ->get(route('academic.periods.index', [
            'year_search' => '2026',
            'term_search' => 'Ganjil',
            'per_page' => 10,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('modules/academic-period/pages/Index')
            ->where('years.data.0.code', '2026-2027')
            ->where('years.data.0.status', 'active')
            ->where('terms.data.0.code', '2026-2027-GANJIL')
            ->where('terms.data.0.is_active', true)
            ->where('currentTerm.code', '2026-2027-GANJIL')
            ->where('filters.year_search', '2026')
            ->where('filters.term_search', 'Ganjil')
            ->where('filters.per_page', '10')
            ->where('canManage', false));
});
