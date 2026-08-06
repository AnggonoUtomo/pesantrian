<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Application\Events\AccessControlActivityOccurred;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\UserManagement\Application\Events\UserManagementActivityOccurred;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
});

it('mencatat integration event UserManagement dengan envelope yang sama', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $eventId = (string) Str::ulid();
    $correlationId = (string) Str::ulid();
    $subjectId = (string) Str::ulid();

    event(new UserManagementActivityOccurred(
        eventName: 'user-management.activity.occurred',
        version: 1,
        eventId: $eventId,
        occurredAt: now()->toIso8601String(),
        correlationId: $correlationId,
        actorId: $actor->id,
        action: 'user.updated',
        subjectType: 'user',
        subjectId: $subjectId,
        reason: null,
        metadata: ['changed_fields' => ['name']],
    ));

    $record = AuditRecord::query()->sole();

    expect($record->event_id)->toBe($eventId)
        ->and($record->correlation_id)->toBe($correlationId)
        ->and($record->subject_id)->toBe($subjectId)
        ->and($record->action)->toBe('user.updated');
});

it('menolak versi integration event yang tidak didukung', function (): void {
    $event = new UserManagementActivityOccurred(
        eventName: 'user-management.activity.occurred',
        version: 2,
        eventId: (string) Str::ulid(),
        occurredAt: now()->toIso8601String(),
        correlationId: (string) Str::ulid(),
        actorId: null,
        action: 'user.updated',
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        reason: null,
        metadata: [],
    );

    expect(fn () => event($event))
        ->toThrow(UnexpectedValueException::class, 'Integration event UserManagement tidak didukung.');

    expect(AuditRecord::query()->count())->toBe(0);
});

it('mencatat mutasi role dan user melalui producer event', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->post(route('access-control.roles.store'), ['name' => 'AuditReviewer'])
        ->assertRedirect();

    $this->actingAs($actor)
        ->post(route('system.users.store'), [
            'name' => 'Audit Test User',
            'email' => 'audit-test@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect();

    expect(AuditRecord::query()->pluck('action')->all())
        ->toContain('access_control.role.created', 'user.created');
});

it('merollback mutasi role ketika consumer audit gagal', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    Event::listen(AccessControlActivityOccurred::class, static function (): never {
        throw new RuntimeException('Audit storage gagal.');
    });

    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($actor)->post(
        route('access-control.roles.store'),
        ['name' => 'HarusRollback'],
    ))->toThrow(RuntimeException::class, 'Audit storage gagal.');

    expect(Role::query()->where('name', 'HarusRollback')->exists())->toBeFalse()
        ->and(AuditRecord::query()->where('action', 'access_control.role.created')->exists())->toBeFalse();
});

it('mencatat start dan end impersonation dengan correlation ID yang sama', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->post(route('system.users.impersonate', $target), [
            'reason' => 'Pemeriksaan support',
        ])
        ->assertRedirect(route('system.dashboard'));

    $this->post(route('system.users.impersonation.leave'))
        ->assertRedirect(route('system.dashboard'));

    $records = AuditRecord::query()
        ->whereIn('action', ['user.impersonation_started', 'user.impersonation_ended'])
        ->orderBy('created_at')
        ->get();

    expect($records)->toHaveCount(2)
        ->and($records[0]->actor_id)->toBe($actor->id)
        ->and($records[1]->actor_id)->toBe($actor->id)
        ->and($records[0]->subject_id)->toBe($target->id)
        ->and($records[0]->correlation_id)->toBe($records[1]->correlation_id)
        ->and($records[0]->reason)->toBe('Pemeriksaan support');
});
