<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(SystemSettingSeeder::class);
});

it('menolak guest dan direct permission non-SuperSystem pada backend', function (): void {
    $this->get('/system/system-settings')->assertRedirect(route('login'));

    $actor = User::factory()->create();
    $actor->givePermissionTo('system_setting.manage');

    $this->actingAs($actor)
        ->get('/system/system-settings')
        ->assertForbidden();

    $this->actingAs($actor)
        ->patchJson('/api/v1/system-settings/api.rate_limit.per_minute', [
            'value' => 75,
            'reason' => 'Tidak boleh lolos.',
        ])->assertForbidden();
});

it('menampilkan typed setting page kepada SuperSystem', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->get(route('system.system-settings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('System/SystemSetting/pages/Index')
            ->has('settings', 26)
            ->where('settings.0.key', 'api.idempotency.retention_hours')
            ->where('settings.0.source', 'database'));
});

it('mengubah setting dari web dan mengirim flash toast', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->patch(route('system.system-settings.update', ['key' => 'api.rate_limit.per_minute']), [
            'value' => 80,
            'reason' => 'Penyesuaian dari halaman SystemSetting.',
        ])
        ->assertRedirect()
        ->assertSessionHas('inertia.flash_data.toast', [
            'type' => 'success',
            'message' => 'SystemSetting berhasil diperbarui.',
        ]);

    $record = SystemSettingRecord::query()->where('key', 'api.rate_limit.per_minute')->firstOrFail();
    expect(json_decode($record->value, true, flags: JSON_THROW_ON_ERROR))->toBe(80);
});

it('mengembalikan 422 untuk value atau reason invalid dan 404 untuk unknown key', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->patchJson(route('api.v1.system-settings.update', ['key' => 'api.rate_limit.per_minute']), [
            'value' => 5000,
            'reason' => '',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['value', 'reason']);

    $this->actingAs($actor)
        ->patchJson('/api/v1/system-settings/unknown.key', [
            'value' => 'x',
            'reason' => 'Unknown key.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertNotFound();
});

it('menolak default pagination yang tidak ada pada pilihan global', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->patchJson(route('api.v1.system-settings.update', ['key' => 'pagination.default_per_page']), [
            'value' => 15,
            'reason' => 'Nilai ini tidak tersedia pada pilihan.',
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('value');
});

it('menyediakan API typed untuk list dan mutation', function (): void {
    $actor = User::query()->where('email', 'super-system@example.test')->firstOrFail();

    $this->actingAs($actor)
        ->getJson(route('api.v1.system-settings.index'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(26, 'data')
        ->assertJsonStructure(['success', 'data' => [['key', 'type', 'value', 'source']]]);

    $this->actingAs($actor)
        ->patchJson(route('api.v1.system-settings.update', ['key' => 'monitoring.external_enabled']), [
            'value' => true,
            'reason' => 'Mengaktifkan capability monitoring.',
            'correlation_id' => (string) Str::ulid(),
        ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.key', 'monitoring.external_enabled')
        ->assertJsonPath('data.value', true);
});

it('mendaftarkan route web pada daftar Ziggy', function (): void {
    $routes = config('ziggy.only', []);

    expect($routes)->toContain('system.system-settings.index')
        ->and($routes)->toContain('system.system-settings.update');
});
