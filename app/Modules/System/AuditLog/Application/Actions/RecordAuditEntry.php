<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Actions;

use App\Modules\System\AuditLog\Application\Contracts\AuditLogRepository;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;
use App\Modules\System\AuditLog\Application\Services\MetadataRedactor;
use App\Modules\System\AuditLog\Infrastructure\Context\AuditSecurityContext;

final readonly class RecordAuditEntry implements AuditRecorder
{
    public function __construct(
        private AuditLogRepository $repository,
        private MetadataRedactor $redactor,
        private AuditSecurityContext $securityContext,
    ) {}

    public function record(AuditEntryData $entry): AuditRecordData
    {
        return $this->repository->record(new AuditEntryData(
            eventId: $entry->eventId,
            actorId: $entry->actorId,
            action: trim($entry->action),
            subjectType: trim($entry->subjectType),
            subjectId: $entry->subjectId,
            module: trim($entry->module),
            correlationId: $entry->correlationId,
            reason: $this->redactor->sanitizeReason($entry->reason),
            metadata: $this->redactor->filter(
                $this->securityContext->merge($entry->action, $entry->metadata),
            ),
            occurredAt: $entry->occurredAt,
        ));
    }
}
