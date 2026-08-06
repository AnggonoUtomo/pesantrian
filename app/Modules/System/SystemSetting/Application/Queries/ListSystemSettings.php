<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Queries;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\DTO\SystemSettingItemData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;

final readonly class ListSystemSettings
{
    public function __construct(
        private SettingDefinitionRegistry $definitions,
        private SystemSettingReader $reader,
    ) {}

    /** @return list<SystemSettingItemData> */
    public function execute(): array
    {
        return array_map(function ($definition): SystemSettingItemData {
            $value = $this->reader->get($definition->key);

            return new SystemSettingItemData(
                key: $definition->key,
                type: $definition->type->value,
                value: $value->value,
                defaultValue: $definition->defaultValue,
                description: $definition->description,
                source: $value->source,
                updatedAt: $value->updatedAt,
                min: $definition->min,
                max: $definition->max,
                options: $definition->options,
                nullable: $definition->nullable,
            );
        }, $this->definitions->all());
    }
}
