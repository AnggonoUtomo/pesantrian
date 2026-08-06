<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UpdateSystemSettingData
{
    public string $key;

    public string $reason;

    public function __construct(
        string $key,
        public mixed $value,
        string $reason,
        public string $correlationId,
    ) {
        $this->key = trim($key);
        $this->reason = $this->sanitizeReason($reason);

        if ($this->key === '') {
            throw new InvalidArgumentException('Key setting wajib diisi.');
        }

        if (! Str::isUlid($this->correlationId)) {
            throw new InvalidArgumentException('Correlation ID wajib berupa ULID.');
        }
    }

    private function sanitizeReason(string $reason): string
    {
        $sanitized = trim(strip_tags($reason));
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized) ?? '';

        if ($sanitized === '' || mb_strlen($sanitized) > 500) {
            throw new InvalidArgumentException('Reason wajib diisi dan maksimal 500 karakter.');
        }

        return $sanitized;
    }
}
