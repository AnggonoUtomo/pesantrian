<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

use App\Modules\System\SystemSetting\Domain\ValueObjects\SettingCategory;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UpdateSystemSettingCategoryData
{
    /** @var array<string, mixed> */
    public array $updates;

    public string $reason;

    public SettingCategory $category;

    /** @param array<array-key, mixed> $updates */
    public function __construct(
        string $category,
        array $updates,
        string $reason,
        public string $correlationId,
    ) {
        $this->category = SettingCategory::tryFrom($category)
            ?? throw new InvalidArgumentException('Kategori SystemSetting tidak valid.');
        $this->updates = $this->validatedUpdates($updates);
        $this->reason = $this->sanitizeReason($reason);

        if ($this->updates === []) {
            throw new InvalidArgumentException('Minimal satu SystemSetting wajib diubah.');
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

    /**
     * @param  array<array-key, mixed>  $updates
     * @return array<string, mixed>
     */
    private function validatedUpdates(array $updates): array
    {
        $validated = [];

        foreach ($updates as $key => $value) {
            if (! is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException('Key SystemSetting wajib diisi.');
            }

            $validated[$key] = $value;
        }

        return $validated;
    }
}
