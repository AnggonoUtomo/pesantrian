<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Events;

final readonly class UserManagementActivityOccurred
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $eventName,
        public int $version,
        public string $eventId,
        public string $occurredAt,
        public string $correlationId,
        public ?string $actorId,
        public string $action,
        public string $subjectType,
        public ?string $subjectId,
        public ?string $reason,
        public array $metadata,
    ) {}
}
