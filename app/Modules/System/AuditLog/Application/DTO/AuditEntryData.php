<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\DTO;

use DateTimeImmutable;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class AuditEntryData
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $eventId,
        public ?string $actorId,
        public string $action,
        public string $subjectType,
        public ?string $subjectId,
        public string $module,
        public string $correlationId,
        public ?string $reason,
        public array $metadata,
        public DateTimeImmutable $occurredAt,
    ) {
        $this->assertUlid($eventId, 'Event ID');
        $this->assertNullableUlid($actorId, 'Actor ID');
        $this->assertNullableUlid($subjectId, 'Subject ID');
        $this->assertUlid($correlationId, 'Correlation ID');
        $this->assertText($action, 'Action', 120);
        $this->assertText($subjectType, 'Subject type', 120);
        $this->assertText($module, 'Module', 120);
    }

    private function assertUlid(string $value, string $label): void
    {
        if (! Str::isUlid($value)) {
            throw new InvalidArgumentException("{$label} wajib berupa ULID.");
        }
    }

    private function assertNullableUlid(?string $value, string $label): void
    {
        if ($value !== null) {
            $this->assertUlid($value, $label);
        }
    }

    private function assertText(string $value, string $label, int $maxLength): void
    {
        $length = mb_strlen(trim($value));

        if ($length === 0 || $length > $maxLength) {
            throw new InvalidArgumentException("{$label} wajib diisi dan maksimal {$maxLength} karakter.");
        }
    }
}
