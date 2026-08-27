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
