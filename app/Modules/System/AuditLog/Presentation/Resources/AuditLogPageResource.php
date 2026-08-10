<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Resources;

use App\Modules\System\AuditLog\Application\DTO\AuditLogPage;
use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;
use App\Modules\System\AuditLog\Presentation\Support\AuditLogOperatorLabels;

final readonly class AuditLogPageResource
{
    public function __construct(private AuditLogPage $page) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (AuditRecordData $record): array => [
                    'actorName' => $record->actorName,
                    'actionLabel' => AuditLogOperatorLabels::action($record->action),
                    'subjectLabel' => AuditLogOperatorLabels::subject($record->subjectType),
                    'moduleLabel' => AuditLogOperatorLabels::module($record->module),
                    'reason' => $record->reason,
                    'securityContext' => AuditLogOperatorLabels::securityContext($record->metadata),
                    'settingChange' => AuditLogOperatorLabels::settingChange($record->metadata),
                    'createdAt' => $record->createdAt->format(DATE_ATOM),
                ],
                $this->page->items,
            ),
            'meta' => [
                'currentPage' => $this->page->currentPage,
                'lastPage' => $this->page->lastPage,
                'perPage' => $this->page->perPage,
                'total' => $this->page->total,
            ],
        ];
    }
}
