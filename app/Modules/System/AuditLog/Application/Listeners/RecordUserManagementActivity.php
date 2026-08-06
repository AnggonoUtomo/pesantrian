<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Listeners;

use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\UserManagement\Application\Events\UserManagementActivityOccurred;
use DateTimeImmutable;
use UnexpectedValueException;

final readonly class RecordUserManagementActivity
{
    public function __construct(private AuditRecorder $recorder) {}

    public function handle(UserManagementActivityOccurred $event): void
    {
        if ($event->eventName !== 'user-management.activity.occurred' || $event->version !== 1) {
            throw new UnexpectedValueException('Integration event UserManagement tidak didukung.');
        }

        $this->recorder->record(new AuditEntryData(
            eventId: $event->eventId,
            actorId: $event->actorId,
            action: $event->action,
            subjectType: $event->subjectType,
            subjectId: $event->subjectId,
            module: 'UserManagement',
            correlationId: $event->correlationId,
            reason: $event->reason,
            metadata: $event->metadata,
            occurredAt: new DateTimeImmutable($event->occurredAt),
        ));
    }
}
