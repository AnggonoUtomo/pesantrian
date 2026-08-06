<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;

interface SettingDefinitionRegistrar
{
    public function register(SettingDefinitionData $definition): void;
}
