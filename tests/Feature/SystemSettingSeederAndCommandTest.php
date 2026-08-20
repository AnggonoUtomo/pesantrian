<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\Queries\ValidateSystemSettings;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Tester\CommandTester;

it('menjalankan seeder module secara idempotent tanpa menimpa override', function (): void {
    $this->seed(SystemSettingSeeder::class);

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    $record->update(['value' => json_encode(125, JSON_THROW_ON_ERROR)]);

    $this->seed(SystemSettingSeeder::class);

    expect(SystemSettingRecord::query()->count())->toBe(26)
        ->and(json_decode($record->fresh()?->value ?? '', true, flags: JSON_THROW_ON_ERROR))->toBe(125);
});

it('memanggil SystemSettingSeeder dari entry point global', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(SystemSettingRecord::query()->count())->toBe(26)
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

    expect(SystemSettingRecord::query()->count())->toBe(26);
});

it('meredaksi nilai sensitif pada output command get table dan json', function (): void {
    $secret = 'dummy-secret-cli-get-8d2d';
    $definitions = app(SettingDefinitionRegistry::class);
    app(SystemSettingRepository::class)->upsert(
        $definitions->definition('mail.password'),
        $secret,
        null,
    );

    Artisan::call('system-setting:get', ['key' => 'mail.password']);
    $tableOutput = Artisan::output();

    expect($tableOutput)
        ->not->toContain($secret)
        ->toContain('mail.password')
        ->toContain('database')
        ->toContain('Rahasia terisi');

    Artisan::call('system-setting:get', ['key' => 'mail.password', '--json' => true]);
    $jsonOutput = Artisan::output();
    $payload = json_decode(trim($jsonOutput), true, flags: JSON_THROW_ON_ERROR);

    expect($jsonOutput)->not->toContain($secret)
        ->and($payload)->toMatchArray([
            'key' => 'mail.password',
            'value' => null,
            'source' => 'database',
            'sensitive' => true,
            'has_value' => true,
        ]);
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

it('meredaksi nilai sensitif pada output json command set', function (): void {
    $secret = 'dummy-secret-cli-set-5ab1';
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $command = Artisan::all()['system-setting:set'];
    $tester = new CommandTester($command);
    $tester->setInputs([$secret]);
    $exitCode = $tester->execute([
        'key' => 'mail.password',
        '--actor' => $actor->id,
        '--reason' => 'Regression test redaksi output.',
        '--value-stdin' => true,
        '--json' => true,
    ]);

    $output = $tester->getDisplay();
    $payload = json_decode(trim($output), true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($output)->not->toContain($secret)
        ->and($payload)->toMatchArray([
            'key' => 'mail.password',
            'value' => null,
            'source' => 'database',
            'sensitive' => true,
            'has_value' => true,
        ]);
});

it('menolak nilai posisi untuk setting sensitif tanpa membocorkannya', function (): void {
    $secret = 'dummy-secret-positional-rejected-2d94';
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $exitCode = Artisan::call('system-setting:set', [
        'key' => 'mail.password',
        'value' => $secret,
        '--actor' => $actor->id,
        '--reason' => 'Nilai posisi sensitif harus ditolak.',
    ]);
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->not->toContain($secret)
        ->and($output)->toContain('tidak boleh memakai argumen posisi')
        ->and(SystemSettingRecord::query()->where('key', 'mail.password')->exists())->toBeFalse();
});

it('menerima nilai sensitif melalui prompt tersembunyi interaktif', function (): void {
    $secret = 'dummy-secret-hidden-prompt-9ea1';
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $this->artisan('system-setting:set', [
        'key' => 'mail.password',
        '--actor' => $actor->id,
        '--reason' => 'Mengisi secret lewat prompt tersembunyi.',
        '--json' => true,
    ])
        ->expectsQuestion('Masukkan nilai sensitif untuk [mail.password]', $secret)
        ->assertSuccessful()
        ->doesntExpectOutputToContain($secret);

    expect(app(SystemSettingRepository::class)->find('mail.password')?->value)->toBe($secret);
});

it('menerima nilai sensitif dari stdin untuk otomasi', function (): void {
    $secret = 'dummy-secret-stdin-automation-4c7b';
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $command = Artisan::all()['system-setting:set'];
    $tester = new CommandTester($command);
    $tester->setInputs([$secret]);
    $exitCode = $tester->execute([
        'key' => 'mail.password',
        '--actor' => $actor->id,
        '--reason' => 'Mengisi secret dari stdin.',
        '--value-stdin' => true,
        '--json' => true,
    ]);

    expect($exitCode)->toBe(0)
        ->and($tester->getDisplay())->not->toContain($secret)
        ->and(app(SystemSettingRepository::class)->find('mail.password')?->value)->toBe($secret);
});

it('mempertahankan literal sensitif dari stdin tanpa type coercion', function (string $secret): void {
    $actor = User::factory()->create();
    Role::create(['name' => 'SuperSystem', 'guard_name' => 'web']);
    $actor->assignRole('SuperSystem');

    $command = Artisan::all()['system-setting:set'];
    $tester = new CommandTester($command);
    $tester->setInputs([$secret]);
    $exitCode = $tester->execute([
        'key' => 'mail.password',
        '--actor' => $actor->id,
        '--reason' => 'Memastikan literal sensitif tidak berubah tipe.',
        '--value-stdin' => true,
        '--json' => true,
    ]);
    $payload = json_decode(trim($tester->getDisplay()), true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload)->toMatchArray([
            'key' => 'mail.password',
            'value' => null,
            'sensitive' => true,
            'has_value' => true,
        ])
        ->and(app(SystemSettingRepository::class)->find('mail.password')?->value)->toBe($secret);
})->with([
    'angka' => '123',
    'boolean' => 'true',
    'null' => 'null',
]);

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
