<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationEnded;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function impersonationPermission(): Permission
{
    $permission = Permission::create([
        'name' => 'user.impersonate',
        'guard_name' => 'web',
    ]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $permission;
}

it('memulai dan mengakhiri impersonation dengan actor restore serta event audit', function (): void {
    Event::fake();
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo(impersonationPermission());
    expect($actor->hasPermissionTo('user.impersonate'))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('impersonate', $target))->toBeTrue();

    $this->actingAs($actor)
        ->post(route('system.users.impersonate', $target), [
            'reason' => 'Permintaan support terverifikasi',
        ])
        ->assertRedirect(route('system.dashboard'));

    $this->assertAuthenticatedAs($target);
    expect(session('impersonation.actor_id'))->toBe($actor->id)
        ->and(session('impersonation.target_id'))->toBe($target->id)
        ->and(session('impersonation.reason'))->toBe('Permintaan support terverifikasi');

    Event::assertDispatched(UserImpersonationStarted::class, function (UserImpersonationStarted $event) use ($actor, $target): bool {
        return $event->actorId === $actor->id
            && $event->targetId === $target->id
            && $event->reason === 'Permintaan support terverifikasi';
    });

    $this->post(route('system.users.impersonation.leave'))
        ->assertRedirect(route('system.dashboard'));

    $this->assertAuthenticatedAs($actor);
    expect(session()->has('impersonation.actor_id'))->toBeFalse()
        ->and(session()->has('impersonation.target_id'))->toBeFalse();

    Event::assertDispatched(UserImpersonationEnded::class, function (UserImpersonationEnded $event) use ($actor, $target): bool {
        return $event->actorId === $actor->id
            && $event->targetId === $target->id
            && $event->reason === 'Permintaan support terverifikasi';
    });
});

it('menolak impersonation tanpa permission dan tanpa reason', function (): void {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->post(route('system.users.impersonate', $target), ['reason' => 'support'])
        ->assertForbidden();

    $actor->givePermissionTo(impersonationPermission());

    $this->actingAs($actor)
        ->post(route('system.users.impersonate', $target), [])
        ->assertSessionHasErrors('reason');
});

it('selalu menolak target SuperSystem', function (): void {
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $actor->givePermissionTo(impersonationPermission());
    $role = Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $target->assignRole($role);

    $this->actingAs($actor)
        ->post(route('system.users.impersonate', $target), [
            'reason' => 'Tidak boleh',
        ])
        ->assertForbidden();

    $this->assertAuthenticatedAs($actor);
});

it('event impersonation tidak membawa password atau token', function (): void {
    $event = new UserImpersonationStarted(
        actorId: '01JACTOR000000000000000001',
        targetId: '01JTARGET000000000000000001',
        reason: 'support request',
        startedAt: now()->toIso8601String(),
    );

    expect(get_object_vars($event))->not->toHaveKeys([
        'password',
        'token',
        'credential',
        'sessionCookie',
    ]);
});
