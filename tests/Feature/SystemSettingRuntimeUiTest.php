<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(SystemSettingSeeder::class);
});

function setRuntimeSetting(string $key, int|bool|string|null $value): void
{
    SystemSettingRecord::query()->where('key', $key)->update([
        'value' => json_encode($value, JSON_THROW_ON_ERROR),
    ]);
}

it('menerapkan branding dan appearance global saat user belum punya preference', function (): void {
    setRuntimeSetting('branding.app_name', 'Starter Enterprise');
    setRuntimeSetting('branding.favicon_path', '/brand/favicon.ico');
    setRuntimeSetting('branding.appearance_default', 'dark');
    setRuntimeSetting('branding.palette_default', 'saffron');
    setRuntimeSetting('branding.typography_default', 'serif');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-app-name="Starter Enterprise"', false)
        ->assertSee('data-default-appearance="dark"', false)
        ->assertSee('data-default-palette="saffron"', false)
        ->assertSee('data-default-typography="serif"', false)
        ->assertSee('data-active-appearance="dark"', false)
        ->assertSee('<link rel="icon" href="/brand/favicon.ico"', false);
});

it('mempertahankan cookie appearance user di atas default global', function (): void {
    setRuntimeSetting('branding.appearance_default', 'dark');

    $this->withUnencryptedCookie('appearance', 'light')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('data-default-appearance="dark"', false)
        ->assertSee('data-active-appearance="light"', false);
});

it('membagikan runtime setting typed melalui Inertia tanpa secret', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    setRuntimeSetting('branding.app_name', 'Starter Enterprise');
    setRuntimeSetting('monitoring.external_enabled', true);
    setRuntimeSetting('operations.rto_hours', 3);
    setRuntimeSetting('operations.rpo_hours', 12);

    $this->actingAs($actor)
        ->get(route('system.system-settings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('name', 'Starter Enterprise')
            ->where('branding.appName', 'Starter Enterprise')
            ->where('branding.logoPath', null)
            ->where('runtime.monitoringExternalRequested', true)
            ->where('runtime.monitoringExternalAvailable', false)
            ->where('runtime.monitoringExternalEnabled', false)
            ->where('runtime.rtoHours', 3)
            ->where('runtime.rpoHours', 12)
            ->missing('branding.secret'));
});

it('memperbarui aktivitas session yang masih valid', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    setRuntimeSetting('security.session.idle_minutes', 5);
    setRuntimeSetting('security.session.absolute_hours', 2);

    $this->actingAs($actor)
        ->withSession([
            'system_setting.session_started_at' => now()->subHour()->timestamp,
            'system_setting.last_activity_at' => now()->subMinutes(4)->timestamp,
        ])
        ->get(route('system.dashboard'))
        ->assertOk();

    expect((int) session('system_setting.last_activity_at'))
        ->toBeGreaterThanOrEqual(now()->subSecond()->timestamp);
    $this->assertAuthenticatedAs($actor);
});

it('mengakhiri session yang melewati idle atau absolute lifetime', function (string $timestampKey): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    setRuntimeSetting('security.session.idle_minutes', 5);
    setRuntimeSetting('security.session.absolute_hours', 2);

    $session = [
        'system_setting.session_started_at' => now()->subHour()->timestamp,
        'system_setting.last_activity_at' => now()->subMinute()->timestamp,
    ];
    $session[$timestampKey] = $timestampKey === 'system_setting.session_started_at'
        ? now()->subHours(3)->timestamp
        : now()->subMinutes(6)->timestamp;

    $this->actingAs($actor)
        ->withSession($session)
        ->get(route('system.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
})->with([
    'idle expiry' => 'system_setting.last_activity_at',
    'absolute expiry' => 'system_setting.session_started_at',
]);

it('memakai default session aman ketika storage SystemSetting hilang', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    Schema::drop('system_settings');

    $this->actingAs($actor)
        ->withSession([
            'system_setting.session_started_at' => now()->subHour()->timestamp,
            'system_setting.last_activity_at' => now()->subMinutes(31)->timestamp,
        ])
        ->get(route('system.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('menyediakan diagnostic runtime tanpa klaim backup production', function (): void {
    $diagnostic = app(SystemRuntimeSettings::class)->current()->diagnostic();

    expect($diagnostic)->toHaveKeys([
        'monitoring_external_enabled',
        'rto_hours',
        'rpo_hours',
    ])->not->toHaveKey('backup_teruji');

    $this->artisan('system-setting:runtime', ['--json' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('monitoring_external_enabled');
});
