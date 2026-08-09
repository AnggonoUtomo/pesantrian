<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Domain\Exceptions\ImmutableAuditRecord;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Str;

function auditEntry(?string $actorId = null, ?string $eventId = null): AuditEntryData
{
    return new AuditEntryData(
        eventId: $eventId ?? (string) Str::ulid(),
        actorId: $actorId,
        action: 'user.created',
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: 'Permintaan administrasi',
        metadata: [
            'changed_fields' => ['name'],
            'password' => 'tidak-boleh-tersimpan',
        ],
        occurredAt: new DateTimeImmutable,
    );
}

it('mencatat audit secara idempotent berdasarkan event ID', function (): void {
    $actor = User::factory()->create();
    $eventId = (string) Str::ulid();
    $recorder = app(AuditRecorder::class);

    $first = $recorder->record(auditEntry($actor->id, $eventId));
    $second = $recorder->record(auditEntry($actor->id, $eventId));

    expect($first->id)->toBe($second->id)
        ->and(AuditRecord::query()->count())->toBe(1)
        ->and($first->metadata)->toBe(['changed_fields' => ['name']]);
});

it('menolak perubahan dan penghapusan audit record', function (): void {
    $record = AuditRecord::query()->create([
        'event_id' => (string) Str::ulid(),
        'actor_id' => null,
        'action' => 'system.checked',
        'subject_type' => 'system',
        'subject_id' => null,
        'module' => 'System',
        'project_id' => null,
        'tenant_id' => null,
        'correlation_id' => (string) Str::ulid(),
        'reason' => null,
        'metadata' => [],
        'created_at' => now(),
    ]);

    expect(fn () => $record->forceFill(['action' => 'system.changed'])->save())
        ->toThrow(ImmutableAuditRecord::class)
        ->and(fn () => $record->delete())
        ->toThrow(ImmutableAuditRecord::class);
});

it('membatasi mass assignment audit pada field record yang diizinkan', function (): void {
    expect((new AuditRecord)->getFillable())->toBe([
        'event_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'module',
        'project_id',
        'tenant_id',
        'correlation_id',
        'reason',
        'metadata',
        'created_at',
    ]);
});

it('mempertahankan audit saat actor dihapus permanen', function (): void {
    $actor = User::factory()->create();
    $record = app(AuditRecorder::class)->record(auditEntry($actor->id));

    $actor->forceDelete();

    expect(AuditRecord::query()->findOrFail($record->id)->actor_id)->toBeNull();
});
