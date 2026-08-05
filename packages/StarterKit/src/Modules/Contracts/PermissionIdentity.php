<?php

declare(strict_types=1);

namespace StarterKit\Modules\Contracts;

use InvalidArgumentException;

final readonly class PermissionIdentity
{
    public function __construct(
        public string $key,
        public string $description,
        public string $module,
        public bool $sensitive,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        foreach (['key', 'description', 'module', 'sensitive'] as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Permission field [$field] is required.");
            }
        }

        if (! is_string($data['key']) || ! preg_match('/^[a-z][a-z0-9_]*(\.[a-z0-9_]+)+$/', $data['key'])) {
            throw new InvalidArgumentException('Permission key must use dot notation.');
        }

        if (! is_string($data['description']) || trim($data['description']) === '') {
            throw new InvalidArgumentException('Permission description is required.');
        }

        if (! is_string($data['module']) || ! preg_match('/^[A-Z][A-Za-z0-9]*$/', $data['module'])) {
            throw new InvalidArgumentException('Permission module must use PascalCase.');
        }

        if (! is_bool($data['sensitive'])) {
            throw new InvalidArgumentException('Permission sensitive must be boolean.');
        }

        return new self($data['key'], $data['description'], $data['module'], $data['sensitive']);
    }
}
