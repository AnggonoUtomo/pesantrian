<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Listeners;

use App\Modules\System\AccessControl\Application\Events\AccessControlActivityOccurred;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use DateTimeImmutable;
use UnexpectedValueException;

final readonly class RecordAccessControlActivity
{
    public function __construct(private AuditRecorder $recorder) {}

    public function handle(AccessControlActivityOccurred $event): void
    {
        if ($event->eventName !== 'access-control.activity.occurred' || $event->version !== 1) {
            throw new UnexpectedValueException('Integration event AccessControl tidak didukung.');
        }

        $this->recorder->record(new AuditEntryData(
            eventId: $event->eventId,
            actorId: $event->actorId,
            action: $event->action,
            subjectType: $event->subjectType,
            subjectId: $event->subjectId,
            module: 'AccessControl',
            correlationId: $event->correlationId,
            reason: $event->reason,
            metadata: $event->metadata,
            occurredAt: new DateTimeImmutable($event->occurredAt),
        ));
    }
}
