<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Application\Services\MetadataRedactor;
use Illuminate\Support\Str;

it('mendefinisikan permission baca audit yang valid', function (): void {
    $permissions = require dirname(__DIR__, 2).'/app/Modules/System/AuditLog/permissions.php';

    expect($permissions)->toBe([
        [
            'key' => 'audit_log.view',
            'description' => 'Melihat audit log sesuai scope actor.',
            'module' => 'AuditLog',
            'sensitive' => false,
        ],
    ]);
});

it('menerima audit entry dengan identitas ULID yang valid', function (): void {
    $entry = new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: (string) Str::ulid(),
        action: 'user.created',
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: null,
        metadata: ['changed_fields' => ['name']],
        occurredAt: new DateTimeImmutable,
    );

    expect($entry->action)->toBe('user.created')
        ->and($entry->module)->toBe('UserManagement');
});

it('menolak audit entry dengan correlation ID yang bukan ULID', function (): void {
    new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: null,
        action: 'system.checked',
        subjectType: 'system',
        subjectId: null,
        module: 'System',
        correlationId: 'bukan-ulid',
        reason: null,
        metadata: [],
        occurredAt: new DateTimeImmutable,
    );
})->throws(InvalidArgumentException::class, 'Correlation ID wajib berupa ULID.');

it('menyaring metadata dengan allowlist dan meredaksi key sensitif secara recursive', function (): void {
    $redactor = new MetadataRedactor;

    $metadata = $redactor->filter([
        'changed_fields' => ['name', 'email'],
        'result' => [
            'status' => 'success',
            'api_token' => 'rahasia',
        ],
        'password' => 'jangan-simpan',
        'email' => 'user@example.test',
    ]);

    expect($metadata)->toBe([
        'changed_fields' => ['name', 'email'],
        'result' => [
            'status' => 'success',
            'api_token' => '[REDACTED]',
        ],
    ]);
});

it('membatasi reason dan nilai metadata yang terlalu panjang', function (): void {
    $redactor = new MetadataRedactor(maxStringLength: 20, maxReasonLength: 30);

    expect($redactor->sanitizeReason('  <b>'.str_repeat('a', 50).'</b>  '))
        ->toBe(str_repeat('a', 30))
        ->and($redactor->filter(['role_name' => str_repeat('b', 40)]))
        ->toBe(['role_name' => str_repeat('b', 20)]);
});
