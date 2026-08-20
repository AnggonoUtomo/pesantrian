<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Application\Events\SystemActivityOccurred;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use App\Modules\System\UserManagement\Presentation\Policies\UserManagementPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('mendaftarkan route list user pada boundary module', function (): void {
    expect(route('system.users.index'))->toContain('/system/users');
});

it('mengizinkan actor dengan user.view dan menolak actor tanpa permission', function (): void {
    $permission = Permission::create([
        'name' => 'user.view',
        'guard_name' => 'web',
    ]);
    $authorized = User::factory()->create();
    $authorized->givePermissionTo($permission);
    $unauthorized = User::factory()->create();

    $this->actingAs($authorized)->get(route('system.users.index'))->assertOk();
    $this->actingAs($unauthorized)->get(route('system.users.index'))->assertForbidden();
});

it('mengirim flash toast setelah mutation user berhasil', function (): void {
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($create);

    $this->actingAs($actor)
        ->post(route('system.users.store'), [
            'name' => 'Toast User',
            'email' => 'toast-user@example.test',
            'password' => 'password',
        ])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'User berhasil dibuat.',
        ]);
});

it('mengirim invitation melalui reset token tanpa mengekspos token', function (): void {
    Notification::fake();
    $invite = Permission::create(['name' => 'user.invite', 'guard_name' => 'web']);
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$invite, $create]);

    $this->actingAs($actor)->post(route('system.users.invitations.store'), [
        'name' => 'Invitation User',
        'email' => 'invitation@example.test',
    ])->assertRedirect()->assertSessionHas('inertia.flash_data.toast.type', 'success');

    $user = User::query()->where('email', 'invitation@example.test')->firstOrFail();
    Notification::assertSentTo($user, ResetPassword::class);
});

it('menolak invitation tanpa permission', function (): void {
    $actor = User::factory()->create();
    $this->actingAs($actor)->post(route('system.users.invitations.store'), ['name' => 'No Access', 'email' => 'no-access@example.test'])->assertForbidden();
    expect(User::query()->where('email', 'no-access@example.test')->exists())->toBeFalse();
});

it('menghapus user baru saat delivery invitation gagal', function (): void {
    $invite = Permission::create(['name' => 'user.invite', 'guard_name' => 'web']);
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$invite, $create]);
    Password::shouldReceive('sendResetLink')->once()->andReturn(Password::INVALID_USER);

    $this->actingAs($actor)
        ->from(route('system.users.index'))
        ->post(route('system.users.invitations.store'), [
            'name' => 'Delivery Failure',
            'email' => 'delivery-failure@example.test',
        ])
        ->assertRedirect(route('system.users.index'))
        ->assertSessionHasErrors([
            'email' => 'Email undangan tidak dapat dikirim. Coba lagi setelah layanan email tersedia.',
        ]);

    expect(User::query()->where('email', 'delivery-failure@example.test')->exists())->toBeFalse();
});

it('menghapus user baru dan menyembunyikan detail transport saat mailer melempar exception', function (): void {
    $invite = Permission::create(['name' => 'user.invite', 'guard_name' => 'web']);
    $create = Permission::create(['name' => 'user.create', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$invite, $create]);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new RuntimeException('internal transport diagnostic sentinel'));

    $this->actingAs($actor)
        ->from(route('system.users.index'))
        ->post(route('system.users.invitations.store'), [
            'name' => 'Transport Failure',
            'email' => 'transport-failure@example.test',
        ])
        ->assertRedirect(route('system.users.index'))
        ->assertSessionHasErrors([
            'email' => 'Email undangan tidak dapat dikirim. Coba lagi setelah layanan email tersedia.',
        ])
        ->assertSessionMissing('internal transport diagnostic sentinel');

    expect(User::query()->where('email', 'transport-failure@example.test')->exists())->toBeFalse();
});

it('mengirim role option typed dan mengizinkan assignment melalui capability publik', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo([$view, $update, $assign]);
    Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);

    $this->actingAs($actor)
        ->get(route('system.users.index'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('roles', 1)
                ->where('roles.0.name', 'SecurityAdmin')
        );

    $this->actingAs($actor)
        ->patch(route('system.users.roles', $target), ['roles' => ['SecurityAdmin']])
        ->assertRedirect();

    expect($target->refresh()->hasRole('SecurityAdmin'))->toBeTrue();
});

it('mengganti beberapa role user secara atomik melalui contract publik', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo([$update, $assign]);
    $securityAdmin = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
    $support = Role::create(['name' => 'Support', 'guard_name' => 'web']);
    $legacy = Role::create(['name' => 'Legacy', 'guard_name' => 'web']);
    $target->assignRole($legacy);

    $this->actingAs($actor)
        ->patch(route('system.users.roles', $target), ['roles' => [$securityAdmin->name, $support->name]])
        ->assertRedirect();

    expect($target->refresh()->getRoleNames()->sort()->values()->all())
        ->toBe(['SecurityAdmin', 'Support']);
});

it('menolak sinkronisasi role kosong tanpa mengubah assignment yang ada', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $assign = Permission::create(['name' => 'access_control.role.assign', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo([$update, $assign]);
    $role = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
    $target->assignRole($role);

    $this->actingAs($actor)
        ->from(route('system.users.index'))
        ->patch(route('system.users.roles', $target), ['roles' => []])
        ->assertRedirect(route('system.users.index'))
        ->assertSessionHasErrors('roles');

    expect($target->refresh()->hasRole($role))->toBeTrue();
});

it('mengirim identity dan access read model dengan fallback aktivitas aman', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $role = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
    $target = User::factory()->create([
        'name' => 'Identity User',
        'email' => 'identity@example.test',
        'email_verified_at' => null,
    ]);
    $target->assignRole($role);

    $this->actingAs($actor)
        ->get(route('system.users.index', ['search' => 'identity']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('users', 1)
                ->where('users.0.name', 'Identity User')
                ->where('users.0.roles', ['SecurityAdmin'])
                ->where('users.0.avatarUrl', null)
                ->where('users.0.emailVerified', false)
                ->where('users.0.lastLoginAt', null)
        );
});

it('memfilter daftar user berdasarkan pencarian, status, role, dan arsip', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    $securityAdmin = Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);
    $matched = User::factory()->create([
        'name' => 'Target Security',
        'email' => 'target-security@example.test',
        'status' => UserStatus::ACTIVE->value,
    ]);
    $matched->assignRole($securityAdmin);

    $inactive = User::factory()->create([
        'name' => 'Target Inactive',
        'email' => 'target-inactive@example.test',
        'status' => UserStatus::INACTIVE->value,
    ]);
    $inactive->assignRole($securityAdmin);

    $archived = User::factory()->create([
        'name' => 'Target Archived',
        'email' => 'target-archived@example.test',
        'status' => UserStatus::ACTIVE->value,
    ]);
    $archived->assignRole($securityAdmin);
    $archived->delete();

    $this->actingAs($actor)
        ->get(route('system.users.index', [
            'search' => 'target',
            'status' => UserStatus::ACTIVE->value,
            'role' => 'SecurityAdmin',
            'archive' => 'active',
        ]))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('users', 1)
                ->where('users.0.email', $matched->email)
                ->where('filters.status', UserStatus::ACTIVE->value)
                ->where('filters.role', 'SecurityAdmin')
                ->where('filters.archive', 'active')
        );

    $this->actingAs($actor)
        ->get(route('system.users.index', ['archive' => 'archived']))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('users', 1)
                ->where('users.0.email', $archived->email)
        );
});

it('memaginasi daftar user di server dan mempertahankan filter aktif', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    User::factory()->count(12)->sequence(
        fn (Sequence $sequence): array => ['name' => "Pagination User {$sequence->index}", 'email' => "pagination-{$sequence->index}@example.test"],
    )->create();

    $this->actingAs($actor)
        ->get(route('system.users.index', [
            'search' => 'Pagination User',
            'page' => 2,
            'per_page' => 5,
        ]))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('users', 5)
                ->where('filters.search', 'Pagination User')
                ->where('filters.page', 2)
                ->where('filters.perPage', 5)
                ->where('pagination.total', 12)
                ->where('pagination.currentPage', 2)
                ->where('pagination.lastPage', 3)
        );
});

it('menolak query filter daftar user yang tidak valid', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);

    $this->actingAs($actor)
        ->get(route('system.users.index', [
            'status' => 'unknown',
            'role' => 'RoleTidakAda',
            'archive' => 'invalid',
            'page' => 0,
            'per_page' => 15,
        ]))
        ->assertInvalid(['status', 'role', 'archive', 'page', 'per_page']);
});

it('memakai konfigurasi pagination global dan menolak ukuran di luar pilihan', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    User::factory()->count(12)->create();
    SystemSettingRecord::query()->where('key', 'pagination.per_page_options')->update(['value' => json_encode([10, 20], JSON_THROW_ON_ERROR)]);
    SystemSettingRecord::query()->where('key', 'pagination.default_per_page')->update(['value' => json_encode(10, JSON_THROW_ON_ERROR)]);

    $this->actingAs($actor)
        ->get(route('system.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users', 10)
            ->where('pagination.defaultPerPage', 10)
            ->where('pagination.perPageOptions', [10, 20]));

    $this->actingAs($actor)
        ->get(route('system.users.index', ['per_page' => 25]))
        ->assertInvalid('per_page');
});

it('menolak assignment role jika actor tidak memiliki permission assignment', function (): void {
    $update = Permission::create(['name' => 'user.update', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo($update);
    Role::create(['name' => 'SecurityAdmin', 'guard_name' => 'web']);

    $this->actingAs($actor)
        ->patch(route('system.users.roles', $target), ['roles' => ['SecurityAdmin']])
        ->assertForbidden();

    expect($target->refresh()->hasRole('SecurityAdmin'))->toBeFalse();
});

it('memulihkan dan menghapus permanen user arsip dengan permission terpisah serta audit', function (): void {
    Event::fake([SystemActivityOccurred::class]);
    $restore = Permission::create(['name' => 'user.restore', 'guard_name' => 'web']);
    $forceDelete = Permission::create(['name' => 'user.force.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$restore, $forceDelete]);
    $restorable = User::factory()->create();
    $forceDeletable = User::factory()->create();
    $restorable->delete();
    $forceDeletable->delete();

    $this->actingAs($actor)
        ->post(route('system.users.restore', $restorable))
        ->assertRedirect();

    expect($restorable->fresh()->trashed())->toBeFalse();
    Event::assertDispatched(
        SystemActivityOccurred::class,
        fn (SystemActivityOccurred $event): bool => $event->action === 'user.restored'
            && $event->subjectId === $restorable->id,
    );

    $this->actingAs($actor)
        ->delete(route('system.users.force-delete', $forceDeletable))
        ->assertRedirect();

    expect(User::query()->withTrashed()->find($forceDeletable->id))->toBeNull();
    Event::assertDispatched(
        SystemActivityOccurred::class,
        fn (SystemActivityOccurred $event): bool => $event->action === 'user.force_deleted'
            && $event->subjectId === $forceDeletable->id,
    );
});

it('menjalankan bulk lifecycle secara atomik dan memberi toast bila target tidak valid', function (): void {
    Event::fake([SystemActivityOccurred::class]);
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $forceDelete = Permission::create(['name' => 'user.force.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$delete, $forceDelete]);
    $first = User::factory()->create();
    $second = User::factory()->create();
    $archived = User::factory()->create();
    $active = User::factory()->create();
    $archived->delete();

    $this->actingAs($actor)
        ->delete(route('system.users.bulk-destroy'), ['user_ids' => [$first->id, $second->id]])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => '2 user berhasil diarsipkan.',
        ]);

    expect($first->fresh()->trashed())->toBeTrue()
        ->and($second->fresh()->trashed())->toBeTrue();

    Event::assertDispatched(
        SystemActivityOccurred::class,
        fn (SystemActivityOccurred $event): bool => $event->action === 'user.deleted',
    );
    Event::assertDispatchedTimes(SystemActivityOccurred::class, 2);

    $this->actingAs($actor)
        ->delete(route('system.users.bulk-force-delete'), ['user_ids' => [$archived->id, $active->id]])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'error',
            'message' => 'Operasi dibatalkan. Semua user terpilih harus sudah diarsipkan dan bukan SuperSystem.',
        ]);

    expect(User::query()->withTrashed()->find($archived->id))->not->toBeNull()
        ->and($active->fresh()->trashed())->toBeFalse();

    $this->actingAs($actor)
        ->delete(route('system.users.bulk-force-delete'), ['user_ids' => [$archived->id, $first->id]])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => '2 user berhasil dihapus permanen.',
        ]);

    expect(User::query()->withTrashed()->find($archived->id))->toBeNull()
        ->and(User::query()->withTrashed()->find($first->id))->toBeNull();

    $superSystemRole = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $protected = User::factory()->create();
    $protected->assignRole($superSystemRole);
    $candidate = User::factory()->create();

    $this->actingAs($actor)
        ->delete(route('system.users.bulk-destroy'), ['user_ids' => [$protected->id, $candidate->id]])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'error',
            'message' => 'Operasi dibatalkan. Semua user terpilih harus masih aktif dan bukan SuperSystem.',
        ]);

    expect($candidate->fresh()->trashed())->toBeFalse();

    $correlationIds = Event::dispatched(SystemActivityOccurred::class)
        ->map(static fn (array $arguments): string => $arguments[0]->correlationId)
        ->unique();

    expect($correlationIds)->toHaveCount(2);
});

it('menolak payload bulk lifecycle yang kosong tanpa mutation', function (): void {
    $delete = Permission::create(['name' => 'user.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($delete);
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->delete(route('system.users.bulk-destroy'), ['user_ids' => []])
        ->assertInvalid('user_ids');

    expect($target->fresh()->trashed())->toBeFalse();
});

it('menolak restore dan force delete untuk user aktif atau SuperSystem', function (): void {
    $restore = Permission::create(['name' => 'user.restore', 'guard_name' => 'web']);
    $forceDelete = Permission::create(['name' => 'user.force.delete', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$restore, $forceDelete]);
    $active = User::factory()->create();
    $superSystem = User::factory()->create();
    $superSystem->assignRole(Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']));
    $superSystem->delete();

    $this->actingAs($actor)
        ->post(route('system.users.restore', $active))
        ->assertForbidden();

    $this->actingAs($actor)
        ->delete(route('system.users.force-delete', $superSystem))
        ->assertForbidden();
});

it('policy menolak mutation terhadap SuperSystem', function (): void {
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $superSystem = User::factory()->create();
    $role = Role::create([
        'name' => 'SuperSystem',
        'guard_name' => 'web',
    ]);
    $superSystem->assignRole($role);

    expect(Gate::forUser($actor)->allows('delete', $target))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('delete', $superSystem))->toBeFalse()
        ->and(app(UserManagementPolicy::class)->delete($actor, $superSystem))->toBeFalse();
});

it('controller hanya menjadi orchestration layer', function (): void {
    $source = file_get_contents(app_path('Modules/System/UserManagement/Presentation/Controllers/UserController.php'));

    expect($source)->not->toContain('::query(')
        ->and($source)->not->toContain('->where(')
        ->and($source)->not->toContain('->get(');
});

it('memberi identifier aksesibel pada semua filter daftar user', function (): void {
    $source = file_get_contents(resource_path('js/pages/System/UserManagement/components/UserTable.tsx'));

    expect($source)->toContain('id="user-filter-status"')
        ->and($source)->toContain('id="user-filter-role"')
        ->and($source)->toContain('id="user-filter-archive"')
        ->and($source)->toContain('name="status"')
        ->and($source)->toContain('name="role"')
        ->and($source)->toContain('name="archive"')
        ->and($source)->toContain('name="per_page"');
});

it('menyembunyikan kontrol avatar untuk user yang diarsipkan', function (): void {
    $source = file_get_contents(resource_path('js/pages/System/UserManagement/components/UserViewDialog.tsx'));

    expect($source)->toContain('user.deletedAt === null');
});

it('tidak menganggap validation error dialog sebagai kegagalan load halaman', function (): void {
    $source = file_get_contents(resource_path('js/pages/System/UserManagement/pages/Index.tsx'));

    expect($source)->toContain('errors?.users')
        ->and($source)->not->toContain('Object.keys(errors).length');
});
