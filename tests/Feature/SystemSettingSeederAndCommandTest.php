<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\SystemSetting\Application\Queries\ValidateSystemSettings;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Database\Seeders\DatabaseSeeder;

it('menjalankan seeder module secara idempotent tanpa menimpa override', function (): void {
    $this->seed(SystemSettingSeeder::class);

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    $record->update(['value' => json_encode(125, JSON_THROW_ON_ERROR)]);

    $this->seed(SystemSettingSeeder::class);

    expect(SystemSettingRecord::query()->count())->toBe(13)
        ->and(json_decode($record->fresh()?->value ?? '', true, flags: JSON_THROW_ON_ERROR))->toBe(125);
});

it('memanggil SystemSettingSeeder dari entry point global', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(SystemSettingRecord::query()->count())->toBe(13)
        ->and(SystemSettingRecord::query()->where('key', 'branding.palette_default')->exists())->toBeTrue();
});

it('menyediakan command list dan get tanpa mengubah data', function (): void {
    $this->seed(SystemSettingSeeder::class);

    $this->artisan('system-setting:list', ['--json' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('api.rate_limit.per_minute');

    $this->artisan('system-setting:get', ['key' => 'api.rate_limit.per_minute', '--json' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('60');

    expect(SystemSettingRecord::query()->count())->toBe(13);
});

it('mengubah setting melalui command dengan SuperSystem dan reason', function (): void {
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $this->artisan('system-setting:set', [
        'key' => 'api.rate_limit.per_minute',
        'value' => '75',
        '--actor' => $actor->id,
        '--reason' => 'Penyesuaian lewat command.',
        '--json' => true,
    ])->assertSuccessful()->expectsOutputToContain('75');

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();

    expect(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(75);
});

it('menolak command set tanpa SuperSystem atau reason', function (): void {
    $actor = User::factory()->create();

    $this->artisan('system-setting:set', [
        'key' => 'api.rate_limit.per_minute',
        'value' => '75',
        '--actor' => $actor->id,
        '--reason' => 'Tidak berwenang.',
    ])->assertFailed();

    $this->artisan('system-setting:set', [
        'key' => 'api.rate_limit.per_minute',
        'value' => '75',
        '--actor' => $actor->id,
    ])->assertFailed();

    expect(SystemSettingRecord::query()->count())->toBe(0);
});

it('mendeteksi record unknown dan invalid melalui command validate', function (): void {
    $this->seed(SystemSettingSeeder::class);

    SystemSettingRecord::query()->create([
        'key' => 'unknown.key',
        'value' => json_encode('unknown', JSON_THROW_ON_ERROR),
        'type' => 'string',
        'description' => 'Record tidak terdaftar.',
        'is_sensitive' => false,
        'updated_by' => null,
    ]);
    SystemSettingRecord::query()
        ->where('key', 'operations.rto_hours')
        ->update(['value' => json_encode(100, JSON_THROW_ON_ERROR)]);

    $report = app(ValidateSystemSettings::class)->execute();

    expect($report->unknown)->toContain('unknown.key')
        ->and($report->invalid)->toContain('operations.rto_hours');

    $this->artisan('system-setting:validate', ['--json' => true])
        ->assertFailed()
        ->expectsOutputToContain('unknown.key');
});
