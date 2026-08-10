<?php

declare(strict_types=1);

use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Domain\ValueObjects\SettingType;

it('mendaftarkan catalog setting baseline dengan default yang valid', function (): void {
    $registry = new SettingDefinitionRegistry('Starter 13');

    expect($registry->all())->toHaveCount(26)
        ->and($registry->definition('api.rate_limit.per_minute')->defaultValue)->toBe(60)
        ->and($registry->definition('branding.app_name')->defaultValue)->toBe('Starter 13')
        ->and($registry->definition('operations.rto_hours')->defaultValue)->toBe(4);
});

it('menormalisasi integer dan menolak nilai di luar range', function (): void {
    $registry = new SettingDefinitionRegistry('Starter 13');
    $definition = $registry->definition('security.session.idle_minutes');

    expect($definition->normalize('45'))->toBe(45);

    $definition->normalize('2');
})->throws(InvalidArgumentException::class, 'Nilai security.session.idle_minutes minimal 5.');

it('menolak path branding yang dapat memuat resource eksternal atau traversal', function (string $path): void {
    $registry = new SettingDefinitionRegistry('Starter 13');

    $registry->definition('branding.logo_path')->normalize($path);
})->with([
    'external URL' => 'https://example.test/logo.svg',
    'traversal' => '/../private/logo.svg',
    'data URL' => 'data:image/svg+xml;base64,AAAA',
])->throws(InvalidArgumentException::class, 'Nilai branding.logo_path wajib berupa path lokal yang aman.');

it('menolak duplicate key dengan contract berbeda', function (): void {
    $registry = new SettingDefinitionRegistry('Starter 13');

    $registry->register(new SettingDefinitionData(
        key: 'operations.rto_hours',
        type: SettingType::Integer,
        defaultValue: 8,
        description: 'Duplikat tidak diizinkan.',
        ownerModule: 'Probe',
        min: 1,
        max: 24,
    ));
})->throws(LogicException::class, 'Definition setting [operations.rto_hours] sudah terdaftar.');

it('menolak key yang tidak terdaftar', function (): void {
    (new SettingDefinitionRegistry('Starter 13'))->definition('unknown.key');
})->throws(InvalidArgumentException::class, 'Setting [unknown.key] tidak terdaftar.');
