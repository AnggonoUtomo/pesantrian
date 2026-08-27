<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Inertia\Testing\AssertableInertia as Assert;

it('menolak actor tanpa permission organization view', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('organization.units.index'))
        ->assertForbidden();
});

it('menampilkan halaman Inertia daftar unit organisasi', function (): void {
    $view = Permission::create(['name' => 'organization.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
        'location_name' => 'Komplek Pusat',
    ]);

    $this->actingAs($actor)
        ->get(route('organization.units.index', [
            'search' => 'Saka',
            'filter' => [
                'status' => 'active',
                'type' => 'foundation',
            ],
            'per_page' => 10,
            'sort' => 'name',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Organization/Organization/pages/Index')
            ->where('units.data.0.code', 'YA')
            ->where('units.data.0.name', 'Yayasan Saka')
            ->where('units.data.0.type', 'foundation')
            ->where('units.data.0.status', 'active')
            ->where('units.meta.total', 1)
            ->where('filters.search', 'Saka')
            ->where('filters.filter.status', 'active')
            ->where('filters.filter.type', 'foundation')
            ->where('filters.per_page', '10')
            ->where('filters.sort', 'name'));
});

it('mengirim parent option dan menampilkan parent id unit organisasi', function (): void {
    $view = Permission::create(['name' => 'organization.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $parent = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'A Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
    ]);
    $child = OrganizationUnitRecord::query()->create([
        'parent_id' => $parent->id,
        'code' => 'PST',
        'name' => 'Pesantren Saka',
        'type' => 'pesantren',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->get(route('organization.units.index', ['search' => 'Pesantren']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('units.data.0.id', $child->id)
            ->where('units.data.0.parent_id', $parent->id)
            ->where('parentOptions.0.id', $parent->id)
            ->where('parentOptions.0.name', 'A Yayasan Saka')
            ->where('parentOptions.0.code', 'YA'));
});

it('membuat dan memperbarui unit organisasi melalui form Inertia', function (): void {
    $manage = Permission::create(['name' => 'organization.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $this->actingAs($actor)
        ->from(route('organization.units.index'))
        ->post(route('organization.units.store'), [
            'parent_id' => null,
            'code' => 'PST',
            'name' => 'Pesantren Saka Tunggal',
            'type' => 'pesantren',
            'status' => 'active',
            'location_name' => 'Kampus Utama',
        ])
        ->assertRedirect(route('organization.units.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Unit organisasi berhasil dibuat.',
        ]);

    $unit = OrganizationUnitRecord::query()->where('code', 'PST')->firstOrFail();
    $parent = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->from(route('organization.units.index'))
        ->put(route('organization.units.update', $unit->id), [
            'parent_id' => $parent->id,
            'code' => 'PST',
            'name' => 'Pesantren Saka Utama',
            'type' => 'pesantren',
            'status' => 'inactive',
            'location_name' => 'Kampus Timur',
        ])
        ->assertRedirect(route('organization.units.index'))
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'Unit organisasi berhasil diperbarui.',
        ]);

    $this->assertDatabaseHas('organization_units', [
        'id' => $unit->id,
        'parent_id' => $parent->id,
        'code' => 'PST',
        'name' => 'Pesantren Saka Utama',
        'status' => 'inactive',
        'location_name' => 'Kampus Timur',
    ]);
});

it('menolak form mutation unit organisasi tanpa permission manage', function (): void {
    $actor = User::factory()->create();
    $unit = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
    ]);

    $this->actingAs($actor)
        ->post(route('organization.units.store'), [
            'code' => 'NOPE',
            'name' => 'Tanpa Izin',
            'type' => 'other',
            'status' => 'active',
        ])
        ->assertForbidden();

    $this->actingAs($actor)
        ->put(route('organization.units.update', $unit->id), [
            'name' => 'Tidak Berubah',
        ])
        ->assertForbidden();
});

it('menghubungkan kontrol create dan edit UI ke permission organization manage', function (): void {
    $page = file_get_contents(resource_path('js/pages/Organization/Organization/pages/Index.tsx'));
    $dialog = file_get_contents(resource_path('js/pages/Organization/Organization/components/OrganizationUnitFormDialog.tsx'));

    expect($page)->toContain("canAccess(auth, 'organization.manage')")
        ->and($page)->toContain('Tambah unit')
        ->and($page)->toContain('onEdit')
        ->and($page)->toContain('parentOptions')
        ->and($dialog)->toContain("route('organization.units.store')")
        ->and($dialog)->toContain("route('organization.units.update', unit.id)")
        ->and($dialog)->toContain('parent_id')
        ->and($dialog)->toContain('organization-unit-parent')
        ->and($dialog)->toContain('htmlFor="organization-unit-code"')
        ->and($dialog)->toContain('htmlFor="organization-unit-name"')
        ->and($dialog)->toContain('role="alert"');
});
