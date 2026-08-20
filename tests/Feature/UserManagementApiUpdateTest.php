<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('memperbarui profile parsial dengan audit dan correlation canonical', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($update);
    $target = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'profile-lama@example.test',
    ]);
    $correlationId = (string) Str::ulid();

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'name' => 'Nama Baru API',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $correlationId,
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Nama Baru API')
        ->assertJsonPath('data.email', 'profile-lama@example.test')
        ->assertJsonPath('meta.correlation_id', $correlationId);

    $audit = AuditRecord::query()->where('action', 'user.updated')->firstOrFail();

    expect($target->refresh()->name)->toBe('Nama Baru API')
        ->and($audit->correlation_id)->toBe($correlationId);
});

it('memperbarui status dengan permission khusus dan menolak status yang sama', function (): void {
    $statusPermission = Permission::create(['name' => 'user.status.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($statusPermission);
    $target = User::factory()->create(['status' => UserStatus::ACTIVE]);

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'status' => 'suspended',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('data.status', 'suspended');

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'status' => 'suspended',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertConflict()
        ->assertJsonPath('code', 'CONFLICT');

    expect(AuditRecord::query()->where('action', 'user.status_changed')->count())->toBe(1);
});

it('memperbarui profile dan status secara atomik dengan dua permission', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $statusPermission = Permission::create(['name' => 'user.status.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$update, $statusPermission]);
    $target = User::factory()->create([
        'name' => 'Atomic Lama',
        'email' => 'atomic-lama@example.test',
        'status' => UserStatus::ACTIVE,
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'email' => 'atomic-baru@example.test',
        'status' => 'inactive',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('data.email', 'atomic-baru@example.test')
        ->assertJsonPath('data.status', 'inactive');

    expect(AuditRecord::query()->whereIn('action', ['user.updated', 'user.status_changed'])->count())->toBe(2);
});

it('me-rollback profile ketika transisi status gabungan gagal', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $statusPermission = Permission::create(['name' => 'user.status.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$update, $statusPermission]);
    $target = User::factory()->create([
        'email' => 'atomic-rollback-lama@example.test',
        'status' => UserStatus::ACTIVE,
    ]);

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'email' => 'atomic-rollback-baru@example.test',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertConflict()
        ->assertJsonPath('code', 'CONFLICT');

    expect($target->refresh()->email)->toBe('atomic-rollback-lama@example.test')
        ->and(AuditRecord::query()->whereIn('action', ['user.updated', 'user.status_changed'])->count())->toBe(0);
});

it('menolak permission field-specific protected target not found duplicate dan validation', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $statusPermission = Permission::create(['name' => 'user.status.manage', 'guard_name' => 'web']);
    $profileActor = User::factory()->create();
    $profileActor->givePermissionTo($update);
    $statusActor = User::factory()->create();
    $statusActor->givePermissionTo($statusPermission);
    $target = User::factory()->create(['email' => 'update-target@example.test']);
    $duplicate = User::factory()->create(['email' => 'update-duplicate@example.test']);
    $protectedRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protected = User::factory()->create();
    $protected->assignRole($protectedRole);

    $this->actingAs($profileActor)->patchJson(route('api.v1.users.update', $target->id), [
        'status' => 'suspended',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($statusActor)->patchJson(route('api.v1.users.update', $target->id), [
        'name' => 'Tidak Boleh',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $this->actingAs($profileActor)->patchJson(route('api.v1.users.update', $protected->id), [
        'name' => 'Protected Berubah',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertConflict()
        ->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($profileActor)->patchJson(route('api.v1.users.update', (string) Str::ulid()), [
        'name' => 'Tidak Ada',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertNotFound()
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

    $this->actingAs($profileActor)->patchJson(route('api.v1.users.update', $target->id), [
        'email' => $duplicate->email,
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertConflict()
        ->assertJsonPath('code', 'CONFLICT');

    $this->actingAs($profileActor)->patchJson(route('api.v1.users.update', $target->id), [], [
        'Idempotency-Key' => (string) Str::ulid(),
    ])->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    expect($target->refresh()->name)->not->toBe('Tidak Boleh')
        ->and($protected->refresh()->name)->not->toBe('Protected Berubah');
});

it('mereplay update tanpa audit duplikat dan menolak payload berbeda', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($update);
    $target = User::factory()->create();
    $headers = ['Idempotency-Key' => (string) Str::ulid()];

    $first = $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'name' => 'Replay Update',
    ], $headers)->assertOk();
    $second = $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'name' => 'Replay Update',
    ], $headers)->assertOk()->assertHeader('Idempotency-Replayed', 'true');

    expect($second->json())->toBe($first->json())
        ->and(AuditRecord::query()->where('action', 'user.updated')->count())->toBe(1);

    $this->actingAs($actor)->patchJson(route('api.v1.users.update', $target->id), [
        'name' => 'Payload Berbeda',
    ], $headers)->assertConflict()->assertJsonPath('code', 'IDEMPOTENCY_CONFLICT');
});
