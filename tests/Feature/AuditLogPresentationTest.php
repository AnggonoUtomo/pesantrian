<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function presentationAuditEntry(string $actorId, string $action = 'user.updated'): AuditEntryData
{
    return new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $actorId,
        action: $action,
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: null,
        metadata: ['changed_fields' => ['name']],
        occurredAt: new DateTimeImmutable,
    );
}

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
});

it('menolak actor yang tidak memiliki permission audit', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('system.audit-logs.index'))
        ->assertForbidden();
});

it('hanya menampilkan audit milik actor biasa', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $actor->givePermissionTo('audit_log.view');
    app(AuditRecorder::class)->record(presentationAuditEntry($actor->id, 'user.updated'));
    app(AuditRecorder::class)->record(presentationAuditEntry($other->id, 'user.deleted'));

    $this->actingAs($actor)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('System/AuditLog/pages/Index')
            ->has('auditLogs.data', 1)
            ->where('auditLogs.data.0.action', 'user.updated')
            ->where('auditLogs.meta.total', 1));
});

it('memberikan seluruh audit kepada SuperSystem', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $other = User::factory()->create();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id));
    app(AuditRecorder::class)->record(presentationAuditEntry($other->id));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('auditLogs.data', 2)
            ->where('auditLogs.meta.total', 2));
});

it('mengembalikan 404 untuk detail di luar scope actor', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $actor->givePermissionTo('audit_log.view');
    $record = app(AuditRecorder::class)->record(presentationAuditEntry($other->id));

    $this->actingAs($actor)
        ->get(route('system.audit-logs.show', $record->id))
        ->assertNotFound();
});

it('memfilter audit dan membatasi nilai per page', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id, 'user.updated'));
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id, 'user.deleted'));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', [
            'action' => 'user.deleted',
            'per_page' => 999,
        ]))
        ->assertSessionHasErrors('per_page');

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', ['action' => 'user.deleted']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('auditLogs.data', 1)
            ->where('auditLogs.data.0.action', 'user.deleted'));
});

it('menyediakan response envelope pada API internal', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id));

    $this->actingAs($superSystem)
        ->getJson(route('api.v1.audit-logs.index'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonStructure(['success', 'data' => ['data', 'meta']]);
});
