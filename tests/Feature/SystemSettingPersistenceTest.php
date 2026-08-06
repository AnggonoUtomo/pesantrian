<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\Services\DatabaseSystemSettingReader;
use App\Modules\System\SystemSetting\Application\Services\RequestSettingMemoizer;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories\EloquentSystemSettingRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

it('membuat schema SystemSetting dan idempotency dengan ULID serta index utama', function (): void {
    expect(Schema::hasColumns('system_settings', [
        'id', 'key', 'value', 'type', 'description', 'is_sensitive',
        'updated_by', 'created_at', 'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('idempotency_keys', [
            'id', 'actor_id', 'key', 'endpoint', 'payload_hash',
            'response_status', 'response_body', 'expires_at', 'created_at',
        ]))->toBeTrue();
});

it('membaca nilai database yang valid melalui public reader', function (): void {
    $actor = User::factory()->create();

    SystemSettingRecord::query()->create([
        'id' => (string) Str::ulid(),
        'key' => 'api.rate_limit.per_minute',
        'value' => json_encode(90, JSON_THROW_ON_ERROR),
        'type' => 'integer',
        'description' => 'Batas request.',
        'is_sensitive' => false,
        'updated_by' => $actor->id,
    ]);

    $value = app(SystemSettingReader::class)->get('api.rate_limit.per_minute');

    expect($value->value)->toBe(90)
        ->and($value->source)->toBe('database')
        ->and($value->updatedAt)->not->toBeNull();
});

it('memakai default aman saat record belum ada atau invalid', function (): void {
    $reader = app(SystemSettingReader::class);

    expect($reader->integer('api.rate_limit.per_minute'))->toBe(60);

    SystemSettingRecord::query()->create([
        'id' => (string) Str::ulid(),
        'key' => 'operations.rto_hours',
        'value' => json_encode(100, JSON_THROW_ON_ERROR),
        'type' => 'integer',
        'description' => 'Record invalid untuk test.',
        'is_sensitive' => false,
        'updated_by' => null,
    ]);

    expect($reader->integer('operations.rto_hours'))->toBe(4)
        ->and($reader->get('operations.rto_hours')->source)->toBe('default');
});

it('mempertahankan setting ketika actor pembaru dihapus permanen', function (): void {
    $actor = User::factory()->create();
    $record = SystemSettingRecord::query()->create([
        'id' => (string) Str::ulid(),
        'key' => 'monitoring.external_enabled',
        'value' => json_encode(false, JSON_THROW_ON_ERROR),
        'type' => 'boolean',
        'description' => 'Flag monitoring.',
        'is_sensitive' => false,
        'updated_by' => $actor->id,
    ]);

    $actor->forceDelete();

    expect($record->fresh()?->updated_by)->toBeNull();
});

it('mempertahankan bentuk scalar dan object pada storage JSON', function (): void {
    SystemSettingRecord::query()->insert([
        [
            'id' => (string) Str::ulid(),
            'key' => 'test.scalar',
            'value' => json_encode(true, JSON_THROW_ON_ERROR),
            'type' => 'boolean',
            'description' => 'Scalar test.',
            'is_sensitive' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => (string) Str::ulid(),
            'key' => 'test.object',
            'value' => json_encode(['enabled' => true], JSON_THROW_ON_ERROR),
            'type' => 'object',
            'description' => 'Object test.',
            'is_sensitive' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $repository = app(EloquentSystemSettingRepository::class);

    expect($repository->find('test.scalar')?->value)->toBeTrue()
        ->and($repository->find('test.object')?->value)->toBe(['enabled' => true]);
});

it('melakukan memoization pembacaan selama satu request', function (): void {
    $repository = Mockery::mock(SystemSettingRepository::class);
    $repository->shouldReceive('find')
        ->once()
        ->with('api.rate_limit.per_minute')
        ->andReturnNull();

    $reader = new DatabaseSystemSettingReader(
        app(SettingDefinitionRegistry::class),
        $repository,
        new RequestSettingMemoizer,
        Mockery::mock(LoggerInterface::class),
    );

    expect($reader->integer('api.rate_limit.per_minute'))->toBe(60)
        ->and($reader->integer('api.rate_limit.per_minute'))->toBe(60);
});

it('membaca banyak setting melalui satu panggilan repository', function (): void {
    $keys = [
        'branding.app_name',
        'security.session.idle_minutes',
        'branding.app_name',
    ];
    $repository = Mockery::mock(SystemSettingRepository::class);
    $repository->shouldReceive('findMany')
        ->once()
        ->with([
            'branding.app_name',
            'security.session.idle_minutes',
        ])
        ->andReturn([]);

    $reader = new DatabaseSystemSettingReader(
        app(SettingDefinitionRegistry::class),
        $repository,
        new RequestSettingMemoizer,
        Mockery::mock(LoggerInterface::class),
    );

    $values = $reader->many($keys);

    expect(array_keys($values))->toBe([
        'branding.app_name',
        'security.session.idle_minutes',
    ])->and($values['branding.app_name']->source)->toBe('default')
        ->and($values['security.session.idle_minutes']->value)->toBe(30);
});

it('memakai default dan diagnostic aman saat storage gagal', function (): void {
    $repository = Mockery::mock(SystemSettingRepository::class);
    $repository->shouldReceive('find')
        ->once()
        ->andThrow(new SettingStorageUnavailable('credential-rahasia'));

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('warning')
        ->once()
        ->with('SystemSetting memakai default aman.', Mockery::on(
            static fn (array $context): bool => $context === [
                'setting_key' => 'api.rate_limit.per_minute',
                'failure_type' => SettingStorageUnavailable::class,
            ],
        ));

    $reader = new DatabaseSystemSettingReader(
        app(SettingDefinitionRegistry::class),
        $repository,
        new RequestSettingMemoizer,
        $logger,
    );

    expect($reader->integer('api.rate_limit.per_minute'))->toBe(60);
});

it('dapat rollback dan membuat ulang schema module', function (): void {
    $migration = require app_path('Modules/System/SystemSetting/Database/Migrations/2026_08_06_200000_create_system_setting_tables.php');

    $migration->down();

    expect(Schema::hasTable('system_settings'))->toBeFalse()
        ->and(Schema::hasTable('idempotency_keys'))->toBeFalse();

    $migration->up();

    expect(Schema::hasTable('system_settings'))->toBeTrue()
        ->and(Schema::hasTable('idempotency_keys'))->toBeTrue();
});
