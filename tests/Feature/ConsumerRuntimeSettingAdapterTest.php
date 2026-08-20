<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Application\Contracts\AuditRuntimeSettings;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Infrastructure\Runtime\SystemSettingAuditRuntimeSettings;
use App\Modules\System\SystemSetting\Infrastructure\Runtime\SystemSettingUserRuntimeSettings;
use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('mengadaptasi pagination SystemSetting ke port consumer', function (): void {
    SystemSettingRecord::query()->where('key', 'pagination.per_page_options')->update([
        'value' => json_encode([10, 20], JSON_THROW_ON_ERROR),
    ]);
    SystemSettingRecord::query()->where('key', 'pagination.default_per_page')->update([
        'value' => json_encode(10, JSON_THROW_ON_ERROR),
    ]);

    $users = app(UserRuntimeSettings::class);
    $audit = app(AuditRuntimeSettings::class);

    expect($users)->toBeInstanceOf(SystemSettingUserRuntimeSettings::class)
        ->and($users->pagination()->perPageOptions)->toBe([10, 20])
        ->and($users->pagination()->defaultPerPage)->toBe(10)
        ->and($audit)->toBeInstanceOf(SystemSettingAuditRuntimeSettings::class)
        ->and($audit->pagination()->perPageOptions)->toBe([10, 20])
        ->and($audit->pagination()->defaultPerPage)->toBe(10);
});

it('mengadaptasi konfigurasi invitation tanpa mengeksposnya ke port AuditLog', function (): void {
    SystemSettingRecord::query()->where('key', 'mail.mailer')->update([
        'value' => json_encode('log', JSON_THROW_ON_ERROR),
    ]);
    SystemSettingRecord::query()->where('key', 'mail.port')->update([
        'value' => json_encode(2525, JSON_THROW_ON_ERROR),
    ]);

    $mail = app(UserRuntimeSettings::class)->invitationMail();

    expect($mail->mailer)->toBe('log')
        ->and($mail->port)->toBe(2525)
        ->and(get_class_methods(AuditRuntimeSettings::class))->toBe(['pagination']);
});

it('memakai default consumer ketika storage pagination rusak', function (): void {
    SystemSettingRecord::query()->where('key', 'pagination.per_page_options')->update([
        'value' => '{json-rusak',
    ]);

    expect(app(UserRuntimeSettings::class)->pagination()->perPageOptions)
        ->toBe([5, 10, 25, 50, 100])
        ->and(app(UserRuntimeSettings::class)->pagination()->defaultPerPage)->toBe(25)
        ->and(app(AuditRuntimeSettings::class)->pagination()->perPageOptions)
        ->toBe([5, 10, 25, 50, 100])
        ->and(app(AuditRuntimeSettings::class)->pagination()->defaultPerPage)->toBe(25);
});
