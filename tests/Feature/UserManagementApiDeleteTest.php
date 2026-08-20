<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('mengarsipkan user dengan reason audit correlation dan response null', function (): void {
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($delete);
    $target = User::factory()->create();
    $correlationId = (string) Str::ulid();

    $this->actingAs($actor)->deleteJson(route('api.v1.users.destroy', $target->id), [
        'reason' => 'Akun tidak lagi digunakan oleh operator.',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $correlationId,
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User berhasil diarsipkan.')
        ->assertJsonPath('data', null)
        ->assertJsonPath('meta.correlation_id', $correlationId);

    $audit = AuditRecord::query()->where('action', 'user.deleted')->firstOrFail();

    expect($target->fresh()->trashed())->toBeTrue()
        ->and($audit->reason)->toBe('Akun tidak lagi digunakan oleh operator.')
        ->and($audit->correlation_id)->toBe($correlationId);
});

it('mereplay delete tanpa mutation atau audit duplikat', function (): void {
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($delete);
    $target = User::factory()->create();
    $headers = ['Idempotency-Key' => (string) Str::ulid()];
    $payload = ['reason' => 'Uji replay pengarsipan user.'];

    $first = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.destroy', $target->id),
        $payload,
        $headers,
    )->assertOk();
    $second = $this->actingAs($actor)->deleteJson(
        route('api.v1.users.destroy', $target->id),
        $payload,
        $headers,
    )->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(AuditRecord::query()->where('action', 'user.deleted')->count())->toBe(1);
});

it('menolak guest permission self protected missing dan target terarsip', function (): void {
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($delete);
    $target = User::factory()->create();
    $protectedRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protected = User::factory()->create();
    $protected->assignRole($protectedRole);
    $archived = User::factory()->create();
    $archived->delete();

    $this->deleteJson(route('api.v1.users.destroy', $target->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)->deleteJson(route('api.v1.users.destroy', $target->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');

    foreach ([$actor->id, $protected->id, $archived->id] as $userId) {
        $this->actingAs($actor)->deleteJson(route('api.v1.users.destroy', $userId), [], [
            'Idempotency-Key' => (string) Str::ulid(),
        ])->assertConflict()->assertJsonPath('code', 'CONFLICT');
    }

    $this->actingAs($actor)->deleteJson(
        route('api.v1.users.destroy', (string) Str::ulid()),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

    expect($target->fresh()->trashed())->toBeFalse()
        ->and($protected->fresh()->trashed())->toBeFalse();
});

it('menolak reason invalid atau sensitif dan me-rollback delete', function (): void {
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($delete);
    $tooLong = User::factory()->create();
    $sensitive = User::factory()->create();

    $this->actingAs($actor)->deleteJson(route('api.v1.users.destroy', $tooLong->id), [
        'reason' => str_repeat('a', 501),
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    $this->actingAs($actor)->deleteJson(route('api.v1.users.destroy', $sensitive->id), [
        'reason' => 'password=dummy-delete-secret',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertDontSee('dummy-delete-secret');

    expect($tooLong->fresh()->trashed())->toBeFalse()
        ->and($sensitive->fresh()->trashed())->toBeFalse()
        ->and(AuditRecord::query()->where('action', 'user.deleted')->count())->toBe(0);
});
