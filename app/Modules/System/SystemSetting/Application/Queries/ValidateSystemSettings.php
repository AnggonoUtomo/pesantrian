<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Queries;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\DTO\SettingValidationReport;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use InvalidArgumentException;

final readonly class ValidateSystemSettings
{
    public function __construct(
        private SettingDefinitionRegistry $definitions,
        private SystemSettingRepository $repository,
    ) {}

    public function execute(): SettingValidationReport
    {
        $stored = $this->repository->all();
        $storedKeys = array_column($stored, 'key');
        $definitionKeys = array_map(static fn ($definition): string => $definition->key, $this->definitions->all());
        $missing = array_values(array_diff($definitionKeys, $storedKeys));
        $invalid = [];
        $unknown = [];

        foreach ($stored as $setting) {
            try {
                $definition = $this->definitions->definition($setting->key);

                if ($setting->type !== $definition->type->value) {
                    $invalid[] = $setting->key;

                    continue;
                }

                $definition->normalize($setting->value);
            } catch (InvalidArgumentException) {
                if (in_array($setting->key, $definitionKeys, true)) {
                    $invalid[] = $setting->key;
                } else {
                    $unknown[] = $setting->key;
                }
            }
        }

        sort($missing);
        sort($invalid);
        sort($unknown);

        return new SettingValidationReport($missing, $invalid, $unknown);
    }
}
