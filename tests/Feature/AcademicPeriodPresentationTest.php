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
            ->component('Academic/AcademicPeriod/pages/Index')
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

it('membuat dan memperbarui tahun akademik melalui form Inertia', function (): void {
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->post(route('academic.periods.years.store'), [
            'code' => '2027-2028',
            'name' => 'Tahun Akademik 2027/2028',
            'starts_on' => '2027-07-01',
            'ends_on' => '2028-06-30',
            'status' => 'draft',
        ])
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Tahun akademik berhasil dibuat.',
        ]);

    $year = AcademicYearRecord::query()->where('code', '2027-2028')->firstOrFail();

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->put(route('academic.periods.years.update', $year->id), [
            'code' => '2027-2028',
            'name' => 'TA 2027/2028',
            'starts_on' => '2027-07-01',
            'ends_on' => '2028-06-30',
            'status' => 'active',
        ])
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Tahun akademik berhasil diperbarui.',
        ]);

    $this->assertDatabaseHas('academic_years', [
        'id' => $year->id,
        'name' => 'TA 2027/2028',
        'status' => 'active',
    ]);
});

it('membuat memperbarui mengaktifkan dan menutup term akademik melalui form Inertia', function (): void {
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $year = AcademicYearRecord::query()->create([
        'code' => '2028-2029',
        'name' => 'Tahun Akademik 2028/2029',
        'starts_on' => '2028-07-01',
        'ends_on' => '2029-06-30',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->post(route('academic.periods.terms.store'), [
            'academic_year_id' => $year->id,
            'code' => '2028-2029-GANJIL',
            'name' => 'Semester Ganjil',
            'sequence' => 1,
            'starts_on' => '2028-07-01',
            'ends_on' => '2028-12-31',
            'status' => 'draft',
        ])
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Term akademik berhasil dibuat.',
        ]);

    $term = AcademicTermRecord::query()->where('code', '2028-2029-GANJIL')->firstOrFail();

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->put(route('academic.periods.terms.update', $term->id), [
            'academic_year_id' => $year->id,
            'code' => '2028-2029-GANJIL',
            'name' => 'Semester Ganjil Utama',
            'sequence' => 1,
            'starts_on' => '2028-07-01',
            'ends_on' => '2028-12-31',
            'status' => 'active',
        ])
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Term akademik berhasil diperbarui.',
        ]);

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->patch(route('academic.periods.terms.activate', $term->id))
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Term akademik berhasil diaktifkan.',
        ]);

    $this->assertDatabaseHas('academic_terms', [
        'id' => $term->id,
        'name' => 'Semester Ganjil Utama',
        'status' => 'active',
        'is_active' => true,
    ]);

    $this->actingAs($actor)
        ->from(route('academic.periods.index'))
        ->patch(route('academic.periods.terms.close', $term->id))
        ->assertRedirect(route('academic.periods.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Term akademik berhasil ditutup.',
        ]);

    $this->assertDatabaseHas('academic_terms', [
        'id' => $term->id,
        'status' => 'closed',
        'is_active' => false,
    ]);
});

it('menolak mutation periode akademik tanpa permission manage', function (): void {
    $view = Permission::create(['name' => 'academic_period.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    $this->actingAs($actor)
        ->post(route('academic.periods.years.store'), [
            'code' => '2029-2030',
            'name' => 'Tahun Akademik 2029/2030',
            'starts_on' => '2029-07-01',
            'ends_on' => '2030-06-30',
            'status' => 'draft',
        ])
        ->assertForbidden();
});
