<?php

declare(strict_types=1);

namespace StarterKit\Generator\Contracts;

use InvalidArgumentException;

final readonly class ModuleGenerationRequest
{
    public function __construct(
        public string $module,
        public string $namespace,
        public string $domain,
        public string $profile = 'default-v1',
        public bool $dryRun = false,
        public bool $force = false,
        public bool $yes = false,
        public bool $extension = false,
        public bool $overwrite = false,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $module = self::stringValue($data, 'module');
        $namespace = self::moduleNamespace($data);
        $domain = $namespace;
        $profile = $data['profile'] ?? 'default-v1';

        if (! is_string($profile) || ! preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $profile)) {
            throw new InvalidArgumentException('profile harus berupa identifier kebab-case yang aman.');
        }

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
            throw new InvalidArgumentException('module harus berupa PascalCase.');
        }

        if (! preg_match('/^[A-Z][A-Za-z0-9]*$/', $namespace)) {
            $key = array_key_exists('namespace', $data) ? 'namespace' : 'domain';

            throw new InvalidArgumentException("$key harus berupa PascalCase.");
        }

        $dryRun = self::boolValue($data, 'dry_run');
        $force = self::boolValue($data, 'force');
        $yes = self::boolValue($data, 'yes');
        $extension = self::boolValue($data, 'extension');
        $overwrite = self::boolValue($data, 'overwrite');

        if ($yes && ! $force) {
            throw new InvalidArgumentException('yes membutuhkan force.');
        }

        if ($overwrite && ! $extension) {
            throw new InvalidArgumentException('overwrite membutuhkan extension.');
        }

        if ($overwrite && ! $force) {
            throw new InvalidArgumentException('overwrite membutuhkan force.');
        }

        if ($overwrite && ! $yes) {
            throw new InvalidArgumentException('overwrite membutuhkan yes.');
        }

        return new self($module, $namespace, $domain, $profile, $dryRun, $force, $yes, $extension, $overwrite);
    }

    /** @param array<string, mixed> $data */
    private static function moduleNamespace(array $data): string
    {
        if (array_key_exists('namespace', $data)) {
            return self::stringValue($data, 'namespace');
        }

        return self::stringValue($data, 'domain');
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
