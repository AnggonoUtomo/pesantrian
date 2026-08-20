<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(SystemSettingSeeder::class);
});

it('memberi envelope dan correlation canonical pada read API existing', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $correlationId = (string) Str::ulid();

    $audit = $this->actingAs($actor)->getJson(
        route('api.v1.audit-logs.index'),
        ['X-Correlation-ID' => $correlationId],
    )->assertOk();
    $settings = $this->actingAs($actor)->getJson(route('api.v1.system-settings.index'))->assertOk();

    $audit->assertHeader('X-Correlation-ID', $correlationId)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar audit berhasil dibaca.')
        ->assertJsonPath('meta.correlation_id', $correlationId)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
    expect($audit->json('data'))->toBeArray()
        ->and($audit->json('data.meta'))->toBeNull();

    $settings->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar SystemSetting berhasil dibaca.')
        ->assertJsonStructure(['success', 'message', 'data', 'meta' => ['correlation_id']]);
});

it('memberi envelope canonical untuk unauthenticated not found dan validation', function (): void {
    $this->getJson(route('api.v1.audit-logs.index'))
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'UNAUTHENTICATED')
        ->assertJsonStructure(['success', 'message', 'errors', 'code', 'meta' => ['correlation_id']]);

    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $this->actingAs($actor)->getJson(route('api.v1.audit-logs.show', (string) Str::ulid()))
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

    $this->actingAs($actor)->patchJson(
        route('api.v1.system-settings.update', 'api.rate_limit.per_minute'),
        ['value' => 61, 'reason' => 'Header idempotency sengaja tidak dikirim.'],
    )->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['success', 'message', 'errors', 'code', 'meta' => ['correlation_id']]);
});

it('meredaksi identifier dan metadata internal pada detail AuditLog API', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $record = app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: (string) $actor->getKey(),
        action: 'user.updated',
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: 'Verifikasi resource aman.',
        metadata: ['credential_marker' => 'tidak-boleh-keluar'],
        occurredAt: new DateTimeImmutable,
    ));

    $response = $this->actingAs($actor)
        ->getJson(route('api.v1.audit-logs.show', $record->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Detail audit berhasil dibaca.')
        ->assertJsonMissingPath('data.id')
        ->assertJsonMissingPath('data.event_id')
        ->assertJsonMissingPath('data.actor_id')
        ->assertJsonMissingPath('data.subject_id')
        ->assertJsonMissingPath('data.correlation_id')
        ->assertJsonMissingPath('data.metadata');

    expect($response->getContent())->not->toContain('tidak-boleh-keluar');
});
