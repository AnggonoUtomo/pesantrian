<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('membuat dan memperbarui asrama melalui API terotorisasi dengan audit', function (): void {
    $manage = Permission::create(['name' => 'asrama.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    seedAsramaMutationReferences();

    $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.store'), [
        'unit_id' => '01K41KRG60H6GTYB56B6T62AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra',
        'gender_policy' => 'male',
        'description' => 'Area santri putra.',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Asrama berhasil dibuat.')
        ->assertJsonPath('data.code', 'ASR-PTR')
        ->assertJsonPath('data.gender_policy', 'male')
        ->assertJsonPath('data.status', 'active');

    $dormitoryId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.update', $dormitoryId), [
        'name' => 'Asrama Putra Utama',
        'description' => 'Asrama putra utama.',
        'status' => 'inactive',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Asrama berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Asrama Putra Utama')
        ->assertJsonPath('data.status', 'inactive');

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.store'), [
        'unit_id' => '01K41KRG60H6GTYB56B6T62AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Duplikat',
        'gender_policy' => 'male',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable();

    expect(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.dormitory.created')
        ->toContain('asrama.dormitory.updated');
});

it('membuat dan memperbarui kamar asrama melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'asrama.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    seedAsramaMutationReferences();
    $dormitory = DormitoryRecord::query()->create([
        'unit_id' => '01K41KRG60H6GTYB56B6T62AB1',
        'code' => 'ASR-PTR',
        'name' => 'Asrama Putra',
        'gender_policy' => 'male',
        'status' => 'active',
    ]);

    $created = $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.rooms.store', $dormitory->id), [
        'code' => 'A-01',
        'name' => 'Kamar A-01',
        'capacity' => 8,
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Kamar asrama berhasil dibuat.')
        ->assertJsonPath('data.code', 'ASR-PTR')
        ->assertJsonPath('data.rooms.0.code', 'A-01')
        ->assertJsonPath('data.rooms.0.capacity', 8);

    $roomId = (string) $created->json('data.rooms.0.id');

    $this->actingAs($actor)->patchJson(route('api.v1.pesantrian.asrama.rooms.update', [$dormitory->id, $roomId]), [
        'name' => 'Kamar A-01 Revisi',
        'capacity' => 10,
        'status' => 'inactive',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Kamar asrama berhasil diperbarui.')
        ->assertJsonPath('data.rooms.0.name', 'Kamar A-01 Revisi')
        ->assertJsonPath('data.rooms.0.capacity', 10)
        ->assertJsonPath('data.rooms.0.status', 'inactive');

    $this->actingAs($actor)->postJson(route('api.v1.pesantrian.asrama.rooms.store', $dormitory->id), [
        'code' => 'A-01',
        'name' => 'Kamar Duplikat',
        'capacity' => 8,
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable();

    expect(AuditRecord::query()->where('module', 'Asrama')->pluck('action')->all())
        ->toContain('asrama.room.created')
        ->toContain('asrama.room.updated');
});

it('menolak actor tanpa permission asrama manage untuk mutation', function (): void {
    $actor = User::factory()->create();

    seedAsramaMutationReferences();

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.asrama.store'), [
            'unit_id' => '01K41KRG60H6GTYB56B6T62AB1',
            'code' => 'ASR-PTR',
            'name' => 'Asrama Putra',
            'gender_policy' => 'male',
            'status' => 'active',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden();
});

it('menolak unit selain dormitory saat membuat asrama', function (): void {
    $manage = Permission::create(['name' => 'asrama.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    seedAsramaMutationReferences();

    $this->actingAs($actor)
        ->postJson(route('api.v1.pesantrian.asrama.store'), [
            'unit_id' => '01K41KRG60H6GTYB56B6T62AB2',
            'code' => 'ASR-MTS',
            'name' => 'Asrama Unit Salah',
            'gender_policy' => 'male',
            'status' => 'active',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unit_id']);
});

function seedAsramaMutationReferences(): void
{
    DB::table('organization_units')->insert([
        [
            'id' => '01K41KRG60H6GTYB56B6T62AB1',
            'code' => 'ASR',
            'name' => 'Asrama Pondok',
            'type' => 'dormitory',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '01K41KRG60H6GTYB56B6T62AB2',
            'code' => 'MTS',
            'name' => 'MTs Saka',
            'type' => 'education_unit',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
