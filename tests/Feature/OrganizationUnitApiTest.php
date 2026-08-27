<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Support\Str;

it('mengembalikan list unit organisasi dengan filter pagination sort dan envelope canonical', function (): void {
    $view = Permission::create(['name' => 'organization.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $foundation = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
        'location_name' => 'Komplek Pusat',
    ]);
    OrganizationUnitRecord::query()->create([
        'code' => 'ARS',
        'name' => 'Arsip Nonaktif',
        'type' => 'operational_unit',
        'status' => 'inactive',
    ]);

    $query = http_build_query([
        'search' => 'Saka',
        'filter' => [
            'status' => 'active',
            'type' => 'foundation',
        ],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'name',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.organization.units.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar unit organisasi berhasil dibaca.')
        ->assertJsonPath('data.0.id', $foundation->id)
        ->assertJsonPath('data.0.code', 'YA')
        ->assertJsonPath('data.0.name', 'Yayasan Saka')
        ->assertJsonPath('data.0.type', 'foundation')
        ->assertJsonPath('data.0.status', 'active')
        ->assertJsonPath('data.0.location_name', 'Komplek Pusat')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'parent_id',
                'code',
                'name',
                'type',
                'status',
                'location_name',
                'created_at',
                'updated_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('membuat dan memperbarui unit organisasi melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'organization.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $parent = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
    ]);

    $created = $this->actingAs($actor)->postJson(route('api.v1.organization.units.store'), [
        'parent_id' => $parent->id,
        'code' => 'PST',
        'name' => 'Pesantren Saka Tunggal',
        'type' => 'pesantren',
        'status' => 'active',
        'location_name' => 'Kampus Utama',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Unit organisasi berhasil dibuat.')
        ->assertJsonPath('data.parent_id', $parent->id)
        ->assertJsonPath('data.code', 'PST')
        ->assertJsonPath('data.name', 'Pesantren Saka Tunggal');

    $unitId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.organization.units.update', $unitId), [
        'name' => 'Pesantren Saka Utama',
        'status' => 'inactive',
        'location_name' => 'Kampus Timur',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Unit organisasi berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Pesantren Saka Utama')
        ->assertJsonPath('data.status', 'inactive')
        ->assertJsonPath('data.location_name', 'Kampus Timur');

    $this->assertDatabaseHas('organization_units', [
        'id' => $unitId,
        'parent_id' => $parent->id,
        'code' => 'PST',
        'name' => 'Pesantren Saka Utama',
        'status' => 'inactive',
    ]);
});

it('menolak guest actor tanpa permission dan payload invalid dengan envelope canonical', function (): void {
    $this->getJson(route('api.v1.organization.units.index'))
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)
        ->getJson(route('api.v1.organization.units.index'))
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $manage = Permission::create(['name' => 'organization.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
    ]);

    $this->actingAs($actor)->postJson(route('api.v1.organization.units.store'), [
        'code' => 'YA',
        'name' => 'Duplikat',
        'type' => 'unknown',
        'status' => 'bad',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['code', 'type', 'status']]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.organization.units.update', (string) Str::ulid()),
        ['name' => 'Missing'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});
