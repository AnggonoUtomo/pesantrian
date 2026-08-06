<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\DTO;

use DateTimeImmutable;

final readonly class AuditRecordData
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $id,
        public string $eventId,
        public ?string $actorId,
        public ?string $actorName,
        public string $action,
        public string $subjectType,
        public ?string $subjectId,
        public string $module,
        public string $correlationId,
        public ?string $reason,
        public array $metadata,
        public DateTimeImmutable $createdAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'eventId' => $this->eventId,
            'actorId' => $this->actorId,
            'actorName' => $this->actorName,
            'action' => $this->action,
            'subjectType' => $this->subjectType,
            'subjectId' => $this->subjectId,
            'module' => $this->module,
            'correlationId' => $this->correlationId,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'createdAt' => $this->createdAt->format(DATE_ATOM),
        ];
    }
}
