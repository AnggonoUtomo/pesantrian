<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Listeners;

use App\Modules\System\AccessControl\Application\Events\SystemActivityOccurred;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use DateTimeImmutable;
use UnexpectedValueException;

final readonly class RecordSystemActivity
{
    private const SUPPORTED_EVENTS = [
        'AccessControl' => 'access-control.activity.occurred',
        'AcademicPeriod' => 'academic-period.activity.occurred',
        'Asrama' => 'asrama.activity.occurred',
        'HumanResource' => 'human-resource.activity.occurred',
        'KelasRombel' => 'kelas-rombel.activity.occurred',
        'Organization' => 'organization.activity.occurred',
        'PenerimaanSantri' => 'penerimaan-santri.activity.occurred',
        'Santri' => 'santri.activity.occurred',
        'UserManagement' => 'user-management.activity.occurred',
    ];

    public function __construct(private AuditRecorder $recorder) {}

    public function handle(SystemActivityOccurred $event): void
    {
        if ((self::SUPPORTED_EVENTS[$event->module] ?? null) !== $event->eventName || $event->version !== 1) {
            throw new UnexpectedValueException('Integration event System tidak didukung.');
        }

        $this->recorder->record(new AuditEntryData(
            eventId: $event->eventId,
            actorId: $event->actorId,
            action: $event->action,
            subjectType: $event->subjectType,
            subjectId: $event->subjectId,
            module: $event->module,
            correlationId: $event->correlationId,
            reason: $event->reason,
            metadata: $event->metadata,
            occurredAt: new DateTimeImmutable($event->occurredAt),
        ));
    }
}
