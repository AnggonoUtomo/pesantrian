<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('mencatat konteks aman saat login berhasil', function (): void {
    $user = User::factory()->create();
    $this->app->instance(Request::class, Request::create('/login', 'POST', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/139.0.0.0 Safari/537.36',
        'REMOTE_ADDR' => '203.0.113.42',
    ]));

    event(new Login('web', $user, false));

    $record = AuditRecord::query()->sole();

    expect($record->action)->toBe('authentication.signed_in')
        ->and($record->subject_type)->toBe('account')
        ->and($record->module)->toBe('Authentication')
        ->and($record->actor_id)->toBe($user->id)
        ->and($record->metadata)->toBe([
            'browser' => 'Chrome di Windows',
            'ip_address' => '203.0.113.42',
        ]);
});

it('tidak mencatat konteks perangkat untuk audit bisnis biasa', function (): void {
    $user = User::factory()->create();
    $this->app->instance(Request::class, Request::create('/system/users', 'PATCH', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/139.0.0.0 Safari/537.36',
        'REMOTE_ADDR' => '203.0.113.42',
    ]));

    app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $user->id,
        action: 'user.updated',
        subjectType: 'user',
        subjectId: $user->id,
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: null,
        metadata: ['changed_fields' => ['name']],
        occurredAt: now()->toDateTimeImmutable(),
    ));

    expect(AuditRecord::query()->sole()->metadata)->toBe([
        'changed_fields' => ['name'],
    ]);
});
