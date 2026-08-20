<?php

declare(strict_types=1);

namespace StarterKit\Modules\Contracts;

use InvalidArgumentException;

final readonly class ModuleManifest
{
    /** @param list<string> $dependencies */
    public function __construct(
        public string $name,
        public string $namespace,
        public string $version,
        public int $schemaVersion,
        public string $status,
        public string $domain,
        public string $path,
        public string $provider,
        public array $dependencies,
        public string $permissionSource,
        public string $configSource,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $required = [
            'name', 'namespace', 'version', 'schema_version', 'status', 'domain',
            'path', 'provider', 'dependencies', 'permission_source', 'config_source',
        ];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Manifest field [$field] is required.");
            }
        }

        if (! is_string($data['name']) || ! preg_match('/^[A-Z][A-Za-z0-9]*$/', $data['name'])) {
            throw new InvalidArgumentException('Manifest name must use PascalCase.');
        }

        $namespaceSegments = is_string($data['namespace']) ? explode('\\', $data['namespace']) : [];

        if ($namespaceSegments === [] || array_filter($namespaceSegments, static fn (string $segment): bool => preg_match('/^[A-Z][A-Za-z0-9]*$/', $segment) === 1) !== $namespaceSegments) {
            throw new InvalidArgumentException('Manifest namespace is invalid.');
        }

        if (! is_string($data['version']) || ! preg_match('/^\d+\.\d+\.\d+$/', $data['version'])) {
            throw new InvalidArgumentException('Manifest version must use semantic versioning.');
        }

        if (! is_int($data['schema_version']) || $data['schema_version'] < 1) {
            throw new InvalidArgumentException('Manifest schema_version must be a positive integer.');
        }

        if (! is_string($data['status']) || ! in_array($data['status'], ['enabled', 'disabled'], true)) {
            throw new InvalidArgumentException('Manifest status must be enabled or disabled.');
        }

        if (! is_string($data['domain']) || ! preg_match('/^[A-Z][A-Za-z0-9]*$/', $data['domain'])) {
            throw new InvalidArgumentException('Manifest domain must use PascalCase.');
        }

        if (! is_string($data['path']) || ! preg_match('#^app/Modules/[A-Z][A-Za-z0-9]*/[A-Z][A-Za-z0-9]*$#', $data['path'])) {
            throw new InvalidArgumentException('Manifest path must use app/Modules/{Domain}/{Module}.');
        }

        if (! is_string($data['provider']) || ! str_ends_with($data['provider'], '\\ServiceProvider')) {
            throw new InvalidArgumentException('Manifest provider must end with ServiceProvider.');
        }

        if (! is_array($data['dependencies']) || array_filter($data['dependencies'], 'is_string') !== $data['dependencies']) {
            throw new InvalidArgumentException('Manifest dependencies must be a list of strings.');
        }

        foreach (['permission_source', 'config_source'] as $field) {
            if (! is_string($data[$field]) || ! preg_match('/^[A-Za-z0-9._-]+\.php$/', $data[$field])) {
                throw new InvalidArgumentException("Manifest [$field] must point to a PHP source file.");
            }
        }

        return new self(
            $data['name'],
            $data['namespace'],
            $data['version'],
            $data['schema_version'],
            $data['status'],
            $data['domain'],
            $data['path'],
            $data['provider'],
            array_values($data['dependencies']),
            $data['permission_source'],
            $data['config_source'],
        );
    }
}
