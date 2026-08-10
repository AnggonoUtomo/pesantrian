<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\SystemSetting\Application\Actions\UpdateSystemSetting;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingCategoryData;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingData;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

function systemSettingSuperSystem(): User
{
    $user = User::factory()->create();
    Role::query()->firstOrCreate(
        ['name' => 'SuperSystem', 'guard_name' => 'web'],
        ['id' => (string) Str::ulid()],
    );
    $user->assignRole('SuperSystem');

    return $user;
}

function systemSettingUpdateData(mixed $value = 90, string $reason = 'Menyesuaikan kapasitas API.'): UpdateSystemSettingData
{
    return new UpdateSystemSettingData(
        key: 'api.rate_limit.per_minute',
        value: $value,
        reason: $reason,
        correlationId: (string) Str::ulid(),
    );
}

it('mengizinkan SuperSystem mengubah setting dan mencatat audit', function (): void {
    $actor = systemSettingSuperSystem();
    $data = systemSettingUpdateData();

    $result = app(UpdateSystemSetting::class)->execute($actor, $data);

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    $audit = AuditRecord::query()->where('action', 'system_setting.updated')->firstOrFail();

    expect($result->value)->toBe(90)
        ->and($result->source)->toBe('database')
        ->and(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(90)
        ->and($record->updated_by)->toBe($actor->id)
        ->and($audit->actor_id)->toBe($actor->id)
        ->and($audit->subject_id)->toBe($record->id)
        ->and($audit->correlation_id)->toBe($data->correlationId)
        ->and($audit->reason)->toBe('Menyesuaikan kapasitas API.')
        ->and($audit->metadata)->toMatchArray([
            'setting_key' => 'api.rate_limit.per_minute',
            'setting_category' => 'API',
            'setting_label' => 'Batas request per actor dan endpoint setiap menit.',
            'before_value' => 60,
            'after_value' => 90,
            'result' => 'updated',
        ]);
});

it('menolak user non-SuperSystem walau memiliki permission manage', function (): void {
    $actor = User::factory()->create();
    Permission::query()->firstOrCreate(
        ['name' => 'system_setting.manage', 'guard_name' => 'web'],
        ['id' => (string) Str::ulid()],
    );
    $actor->givePermissionTo('system_setting.manage');

    expect(fn () => app(UpdateSystemSetting::class)->execute($actor, systemSettingUpdateData()))
        ->toThrow(AuthorizationException::class)
        ->and(SystemSettingRecord::query()->count())->toBe(0)
        ->and(AuditRecord::query()->count())->toBe(0);
});

it('menolak reason kosong dan value invalid sebelum mutation', function (): void {
    $actor = systemSettingSuperSystem();

    expect(fn () => systemSettingUpdateData(reason: '<script></script>'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => app(UpdateSystemSetting::class)->execute($actor, systemSettingUpdateData(2000)))
        ->toThrow(InvalidArgumentException::class)
        ->and(SystemSettingRecord::query()->count())->toBe(0)
        ->and(AuditRecord::query()->count())->toBe(0);
});

it('melakukan rollback setting ketika AuditRecorder gagal', function (): void {
    $actor = systemSettingSuperSystem();
    $action = app(UpdateSystemSetting::class);

    $action->execute($actor, systemSettingUpdateData(70, 'Nilai awal.'));

    $recorder = Mockery::mock(AuditRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('Audit storage gagal.'));
    app()->instance(AuditRecorder::class, $recorder);

    expect(fn () => app(UpdateSystemSetting::class)->execute(
        $actor,
        systemSettingUpdateData(80, 'Perubahan yang harus rollback.'),
    ))->toThrow(RuntimeException::class);

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();

    expect(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(70)
        ->and(AuditRecord::query()->count())->toBe(1);
});

it('menolak idle session yang tidak lebih kecil dari absolute lifetime', function (): void {
    $actor = systemSettingSuperSystem();
    $data = new UpdateSystemSettingData(
        key: 'security.session.idle_minutes',
        value: 720,
        reason: 'Nilai ini harus ditolak oleh consistency rule.',
        correlationId: (string) Str::ulid(),
    );

    expect(fn () => app(UpdateSystemSetting::class)->execute($actor, $data))
        ->toThrow(InvalidArgumentException::class, 'Idle session harus lebih kecil')
        ->and(SystemSettingRecord::query()->where('key', $data->key)->exists())->toBeFalse();
});

it('mengubah satu kategori secara atomik dengan satu alasan dan correlation audit', function (): void {
    $actor = systemSettingSuperSystem();
    $correlationId = (string) Str::ulid();
    $data = new UpdateSystemSettingCategoryData(
        category: 'password',
        updates: [
            'security.password.require_numbers' => true,
            'security.password.require_symbols' => true,
        ],
        reason: 'Menyelaraskan kebijakan password organisasi.',
        correlationId: $correlationId,
    );

    $result = app(UpdateSystemSetting::class)->executeCategory($actor, $data);

    expect($result)->toHaveCount(2)
        ->and(SystemSettingRecord::query()->whereIn('key', array_keys($data->updates))->count())->toBe(2)
        ->and(AuditRecord::query()->where('action', 'system_setting.updated')->count())->toBe(2)
        ->and(AuditRecord::query()->where('correlation_id', $correlationId)->pluck('reason')->unique()->all())
        ->toBe(['Menyelaraskan kebijakan password organisasi.']);
});

it('menolak key lintas kategori tanpa menyimpan perubahan parsial', function (): void {
    $actor = systemSettingSuperSystem();
    $data = new UpdateSystemSettingCategoryData(
        category: 'password',
        updates: [
            'security.password.require_numbers' => true,
            'mail.host' => 'mail.example.test',
        ],
        reason: 'Data lintas kategori harus ditolak.',
        correlationId: (string) Str::ulid(),
    );

    expect(fn () => app(UpdateSystemSetting::class)->executeCategory($actor, $data))
        ->toThrow(InvalidArgumentException::class, 'bukan milik kategori')
        ->and(SystemSettingRecord::query()->count())->toBe(0)
        ->and(AuditRecord::query()->count())->toBe(0);
});

it('memvalidasi konsistensi seluruh kategori sebelum melakukan mutation', function (): void {
    $actor = systemSettingSuperSystem();
    $data = new UpdateSystemSettingCategoryData(
        category: 'session',
        updates: [
            'security.session.idle_minutes' => 720,
            'security.session.absolute_hours' => 12,
        ],
        reason: 'Nilai session tidak boleh disimpan sebagian.',
        correlationId: (string) Str::ulid(),
    );

    expect(fn () => app(UpdateSystemSetting::class)->executeCategory($actor, $data))
        ->toThrow(InvalidArgumentException::class, 'Idle session harus lebih kecil')
        ->and(SystemSettingRecord::query()->count())->toBe(0)
        ->and(AuditRecord::query()->count())->toBe(0);
});

it('melakukan rollback seluruh kategori ketika audit gagal', function (): void {
    $actor = systemSettingSuperSystem();
    $recorder = Mockery::mock(AuditRecorder::class);
    $recorder->shouldReceive('record')->once()->andThrow(new RuntimeException('Audit storage gagal.'));
    app()->instance(AuditRecorder::class, $recorder);
    $data = new UpdateSystemSettingCategoryData(
        category: 'password',
        updates: [
            'security.password.require_numbers' => true,
            'security.password.require_symbols' => true,
        ],
        reason: 'Kegagalan audit harus membatalkan seluruh kategori.',
        correlationId: (string) Str::ulid(),
    );

    expect(fn () => app(UpdateSystemSetting::class)->executeCategory($actor, $data))
        ->toThrow(RuntimeException::class)
        ->and(SystemSettingRecord::query()->count())->toBe(0)
        ->and(AuditRecord::query()->count())->toBe(0);
});
