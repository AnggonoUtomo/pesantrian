<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Services;

use App\Modules\System\AuditLog\Domain\Exceptions\SensitiveAuditReason;

final readonly class MetadataRedactor
{
    /** @var list<string> */
    public const DEFAULT_ALLOWED_KEYS = [
        'role_name',
        'permission_keys',
        'permission_count',
        'changed_fields',
        'from_status',
        'to_status',
        'setting_key',
        'setting_category',
        'setting_label',
        'before_value',
        'after_value',
        'browser',
        'ip_address',
        'result',
    ];

    /** @var list<string> */
    public const DEFAULT_SENSITIVE_PATTERNS = [
        'password',
        'token',
        'secret',
        'credential',
        'authorization',
        'cookie',
        'session',
        'api_key',
    ];

    /**
     * @param  list<string>  $allowedKeys
     * @param  list<string>  $sensitivePatterns
     */
    public function __construct(
        private array $allowedKeys = self::DEFAULT_ALLOWED_KEYS,
        private array $sensitivePatterns = self::DEFAULT_SENSITIVE_PATTERNS,
        private int $maxDepth = 4,
        private int $maxItems = 50,
        private int $maxStringLength = 500,
        private int $maxReasonLength = 500,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function filter(array $metadata): array
    {
        $filtered = [];

        foreach (array_slice($metadata, 0, $this->maxItems, true) as $key => $value) {
            if (! in_array($key, $this->allowedKeys, true)) {
                continue;
            }

            $filtered[$key] = $this->sanitizeValue($value, $key, 1);
        }

        return $filtered;
    }

    public function sanitizeReason(?string $reason): ?string
    {
        if ($reason === null) {
            return null;
        }

        $sanitized = trim(strip_tags($reason));
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized) ?? '';

        if ($sanitized === '') {
            return null;
        }

        if ($this->containsSensitiveReason($sanitized)) {
            throw new SensitiveAuditReason;
        }

        return mb_substr($sanitized, 0, $this->maxReasonLength);
    }

    private function containsSensitiveReason(string $reason): bool
    {
        return preg_match(
            '/\\b(?:password|token|secret|credential|api[_-]?key|authorization|cookie|session)\\s*(?:=|:)|\\bbearer\\s+[a-z0-9._~+\\/-]+/i',
            $reason,
        ) === 1;
    }

    private function sanitizeValue(mixed $value, string $key, int $depth): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if ($depth > $this->maxDepth) {
            return '[TRUNCATED]';
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach (array_slice($value, 0, $this->maxItems, true) as $nestedKey => $nestedValue) {
                $normalizedKey = is_string($nestedKey) ? $nestedKey : (string) $nestedKey;
                $sanitized[$nestedKey] = $this->sanitizeValue($nestedValue, $normalizedKey, $depth + 1);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return mb_substr($value, 0, $this->maxStringLength);
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        return '[FILTERED]';
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = mb_strtolower($key);

        foreach ($this->sensitivePatterns as $pattern) {
            if (str_contains($normalized, mb_strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }
}
