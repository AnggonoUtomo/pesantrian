<?php

declare(strict_types=1);

namespace StarterKit\Generator\Contracts;

use InvalidArgumentException;

final readonly class ModuleGenerationRequest
{
    public function __construct(
        public string $module,
        public string $domain,
        public string $profile = 'default-v1',
        public bool $dryRun = false,
        public bool $force = false,
        public bool $yes = false,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $module = self::stringValue($data, 'module');
        $domain = self::stringValue($data, 'domain');
        $profile = $data['profile'] ?? 'default-v1';

        if (! is_string($profile) || ! preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $profile)) {
            throw new InvalidArgumentException('profile harus berupa identifier kebab-case yang aman.');
        }

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
            throw new InvalidArgumentException('module harus berupa PascalCase.');
        }

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $domain)) {
            throw new InvalidArgumentException('domain harus berupa PascalCase.');
        }

        $dryRun = self::boolValue($data, 'dry_run');
        $force = self::boolValue($data, 'force');
        $yes = self::boolValue($data, 'yes');

        if ($yes && ! $force) {
            throw new InvalidArgumentException('yes membutuhkan force.');
        }

        return new self($module, $domain, $profile, $dryRun, $force, $yes);
    }

    /** @param array<string, mixed> $data */
    private static function stringValue(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException("$key wajib berupa string tidak kosong.");
        }

        return trim($value);
    }

    /** @param array<string, mixed> $data */
    private static function boolValue(array $data, string $key): bool
    {
        $value = $data[$key] ?? false;

        if (! is_bool($value)) {
            throw new InvalidArgumentException("$key harus berupa boolean.");
        }

        return $value;
    }
}
