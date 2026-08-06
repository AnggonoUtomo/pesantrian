<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

use App\Modules\System\SystemSetting\Domain\ValueObjects\SettingType;
use InvalidArgumentException;

final readonly class SettingDefinitionData
{
    /**
     * @param  list<string>  $options
     */
    public function __construct(
        public string $key,
        public SettingType $type,
        public int|bool|string|null $defaultValue,
        public string $description,
        public string $ownerModule,
        public ?int $min = null,
        public ?int $max = null,
        public array $options = [],
        public bool $nullable = false,
    ) {
        if (preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $key) !== 1) {
            throw new InvalidArgumentException("Key setting [{$key}] tidak valid.");
        }

        if (trim($description) === '' || trim($ownerModule) === '') {
            throw new InvalidArgumentException("Definition setting [{$key}] wajib memiliki description dan owner.");
        }

        $this->normalize($defaultValue);
    }

    public function normalize(mixed $value): int|bool|string|null
    {
        if ($value === null || ($this->type === SettingType::Path && trim((string) $value) === '')) {
            if ($this->nullable) {
                return null;
            }

            throw new InvalidArgumentException("Nilai {$this->key} wajib diisi.");
        }

        return match ($this->type) {
            SettingType::Integer => $this->normalizeInteger($value),
            SettingType::Boolean => $this->normalizeBoolean($value),
            SettingType::String => $this->normalizeString($value),
            SettingType::Enum => $this->normalizeEnum($value),
            SettingType::Path => $this->normalizePath($value),
        };
    }

    private function normalizeInteger(mixed $value): int
    {
        if (is_bool($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException("Nilai {$this->key} wajib berupa integer.");
        }

        $normalized = (int) $value;

        if ($this->min !== null && $normalized < $this->min) {
            throw new InvalidArgumentException("Nilai {$this->key} minimal {$this->min}.");
        }

        if ($this->max !== null && $normalized > $this->max) {
            throw new InvalidArgumentException("Nilai {$this->key} maksimal {$this->max}.");
        }

        return $normalized;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($normalized === null) {
            throw new InvalidArgumentException("Nilai {$this->key} wajib berupa boolean.");
        }

        return $normalized;
    }

    private function normalizeString(mixed $value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Nilai {$this->key} wajib berupa string.");
        }

        $normalized = trim($value);
        $length = mb_strlen($normalized);

        if ($this->min !== null && $length < $this->min) {
            throw new InvalidArgumentException("Panjang {$this->key} minimal {$this->min} karakter.");
        }

        if ($this->max !== null && $length > $this->max) {
            throw new InvalidArgumentException("Panjang {$this->key} maksimal {$this->max} karakter.");
        }

        return $normalized;
    }

    private function normalizeEnum(mixed $value): string
    {
        if (! is_string($value) || ! in_array($value, $this->options, true)) {
            throw new InvalidArgumentException("Nilai {$this->key} tidak termasuk pilihan yang diizinkan.");
        }

        return $value;
    }

    private function normalizePath(mixed $value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Nilai {$this->key} wajib berupa path lokal.");
        }

        $normalized = trim($value);
        $lower = strtolower($normalized);

        if (
            ! str_starts_with($normalized, '/')
            || str_contains($normalized, '..')
            || str_contains($normalized, '\\')
            || str_contains($lower, '://')
            || str_starts_with($lower, 'data:')
        ) {
            throw new InvalidArgumentException("Nilai {$this->key} wajib berupa path lokal yang aman.");
        }

        return $normalized;
    }
}
