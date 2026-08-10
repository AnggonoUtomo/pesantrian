<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\SettingDefinitionRegistrar;
use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Domain\Exceptions\UnknownSettingDefinition;
use App\Modules\System\SystemSetting\Domain\ValueObjects\SettingType;
use LogicException;

final class SettingDefinitionRegistry implements SettingDefinitionRegistrar
{
    /** @var array<string, SettingDefinitionData> */
    private array $definitions = [];

    public function __construct(string $appName)
    {
        foreach ($this->baseline($appName) as $definition) {
            $this->register($definition);
        }
    }

    public function register(SettingDefinitionData $definition): void
    {
        if (isset($this->definitions[$definition->key])) {
            throw new LogicException("Definition setting [{$definition->key}] sudah terdaftar.");
        }

        $this->definitions[$definition->key] = $definition;
        ksort($this->definitions);
    }

    public function definition(string $key): SettingDefinitionData
    {
        return $this->definitions[$key]
            ?? throw new UnknownSettingDefinition("Setting [{$key}] tidak terdaftar.");
    }

    /** @return list<SettingDefinitionData> */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /** @return list<SettingDefinitionData> */
    private function baseline(string $appName): array
    {
        return [
            new SettingDefinitionData('api.rate_limit.per_minute', SettingType::Integer, 60, 'Batas request per actor dan endpoint setiap menit.', 'SystemSetting', 1, 1000),
            new SettingDefinitionData('api.idempotency.retention_hours', SettingType::Integer, 24, 'Masa simpan response idempotent dalam jam.', 'SystemSetting', 1, 168),
            new SettingDefinitionData('security.session.idle_minutes', SettingType::Integer, 30, 'Batas session tanpa aktivitas dalam menit.', 'SystemSetting', 5, 1440),
            new SettingDefinitionData('security.session.absolute_hours', SettingType::Integer, 12, 'Batas maksimal umur session dalam jam.', 'SystemSetting', 1, 168),
            new SettingDefinitionData('pagination.per_page_options', SettingType::IntegerList, [5, 10, 25, 50, 100], 'Pilihan jumlah data per halaman yang tersedia pada daftar.', 'SystemSetting', 1, 100),
            new SettingDefinitionData('pagination.default_per_page', SettingType::Integer, 25, 'Jumlah data per halaman saat pengguna belum memilih ukuran.', 'SystemSetting', 1, 100),
            new SettingDefinitionData('branding.app_name', SettingType::String, trim($appName) !== '' ? trim($appName) : 'Laravel', 'Nama aplikasi global.', 'SystemSetting', 1, 80),
            new SettingDefinitionData('branding.logo_path', SettingType::Path, null, 'Path logo lokal default.', 'SystemSetting', nullable: true),
            new SettingDefinitionData('branding.favicon_path', SettingType::Path, '/favicon.ico', 'Path favicon lokal default.', 'SystemSetting'),
            new SettingDefinitionData('branding.palette_default', SettingType::Enum, 'neutral', 'Palette default ketika user belum memilih.', 'SystemSetting', options: ['urban', 'slate', 'gray', 'zinc', 'neutral', 'stone', 'graphite', 'mist', 'harbor', 'quartz', 'aurora', 'saffron', 'ruby', 'forest', 'ocean', 'plum', 'copper']),
            new SettingDefinitionData('branding.typography_default', SettingType::Enum, 'system', 'Typography default aplikasi.', 'SystemSetting', options: ['system', 'sans', 'serif', 'mono']),
            new SettingDefinitionData('branding.appearance_default', SettingType::Enum, 'system', 'Mode warna default ketika user belum memilih.', 'SystemSetting', options: ['system', 'light', 'dark']),
            new SettingDefinitionData('monitoring.external_enabled', SettingType::Boolean, false, 'Flag integrasi monitoring eksternal.', 'SystemSetting'),
            new SettingDefinitionData('operations.rto_hours', SettingType::Integer, 4, 'Target recovery time dalam jam.', 'SystemSetting', 1, 24),
            new SettingDefinitionData('operations.rpo_hours', SettingType::Integer, 24, 'Target recovery point dalam jam.', 'SystemSetting', 1, 168),
        ];
    }
}
