<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Database\Seeders;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use Illuminate\Database\Seeder;

final class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = app(SettingDefinitionRegistry::class);
        $repository = app(SystemSettingRepository::class);

        foreach ($definitions->all() as $definition) {
            if ($repository->find($definition->key) !== null) {
                continue;
            }

            $repository->upsert($definition, $definition->defaultValue, null);
        }
    }
}
