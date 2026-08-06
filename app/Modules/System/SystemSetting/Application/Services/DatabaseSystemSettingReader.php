<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
use App\Modules\System\SystemSetting\Application\DTO\StoredSettingData;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

final class DatabaseSystemSettingReader implements SystemSettingReader
{
    public function __construct(
        private SettingDefinitionRegistry $definitions,
        private SystemSettingRepository $repository,
        private RequestSettingMemoizer $memoizer,
        private LoggerInterface $logger,
    ) {}

    public function get(string $key): SettingValueData
    {
        $definition = $this->definitions->definition($key);

        if ($this->memoizer->has($key)) {
            return $this->memoizer->get($key);
        }

        $value = $this->read($definition);
        $this->memoizer->put($value);

        return $value;
    }

    public function many(array $keys): array
    {
        $values = [];
        $missing = [];

        foreach (array_values(array_unique($keys)) as $key) {
            $this->definitions->definition($key);

            if ($this->memoizer->has($key)) {
                $values[$key] = $this->memoizer->get($key);
            } else {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            try {
                $stored = $this->repository->findMany($missing);

                foreach ($missing as $key) {
                    $value = $this->normalizeStored(
                        $this->definitions->definition($key),
                        $stored[$key] ?? null,
                    );
                    $this->memoizer->put($value);
                    $values[$key] = $value;
                }
            } catch (SettingStorageUnavailable $exception) {
                foreach ($missing as $key) {
                    $definition = $this->definitions->definition($key);
                    $this->logFallback($definition, $exception);
                    $value = $this->default($definition);
                    $this->memoizer->put($value);
                    $values[$key] = $value;
                }
            }
        }

        $orderedValues = [];

        foreach (array_values(array_unique($keys)) as $key) {
            $orderedValues[$key] = $values[$key];
        }

        return $orderedValues;
    }

    public function integer(string $key): int
    {
        $value = $this->get($key)->value;

        if (! is_int($value)) {
            throw new InvalidArgumentException("Setting [{$key}] bukan integer.");
        }

        return $value;
    }

    public function boolean(string $key): bool
    {
        $value = $this->get($key)->value;

        if (! is_bool($value)) {
            throw new InvalidArgumentException("Setting [{$key}] bukan boolean.");
        }

        return $value;
    }

    public function string(string $key): ?string
    {
        $value = $this->get($key)->value;

        if ($value !== null && ! is_string($value)) {
            throw new InvalidArgumentException("Setting [{$key}] bukan string.");
        }

        return $value;
    }

    private function read(SettingDefinitionData $definition): SettingValueData
    {
        try {
            $stored = $this->repository->find($definition->key);

            return $this->normalizeStored($definition, $stored);
        } catch (SettingStorageUnavailable|InvalidArgumentException $exception) {
            $this->logFallback($definition, $exception);
        }

        return $this->default($definition);
    }

    private function normalizeStored(
        SettingDefinitionData $definition,
        ?StoredSettingData $stored,
    ): SettingValueData {
        if ($stored === null || $stored->type !== $definition->type->value) {
            return $this->default($definition);
        }

        try {
            return new SettingValueData(
                key: $definition->key,
                value: $definition->normalize($stored->value),
                source: 'database',
                updatedAt: $stored->updatedAt,
            );
        } catch (InvalidArgumentException $exception) {
            $this->logFallback($definition, $exception);

            return $this->default($definition);
        }
    }

    private function default(SettingDefinitionData $definition): SettingValueData
    {
        return new SettingValueData(
            key: $definition->key,
            value: $definition->defaultValue,
            source: 'default',
        );
    }

    private function logFallback(SettingDefinitionData $definition, \Throwable $exception): void
    {
        $this->logger->warning('SystemSetting memakai default aman.', [
            'setting_key' => $definition->key,
            'failure_type' => $exception::class,
        ]);
    }
}
