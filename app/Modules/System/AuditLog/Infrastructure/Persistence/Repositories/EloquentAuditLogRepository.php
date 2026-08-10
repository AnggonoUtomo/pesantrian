<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Infrastructure\Persistence\Repositories;

use App\Modules\System\AuditLog\Application\Contracts\AuditLogRepository;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Application\DTO\AuditLogFilter;
use App\Modules\System\AuditLog\Application\DTO\AuditLogPage;
use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentAuditLogRepository implements AuditLogRepository
{
    public function record(AuditEntryData $entry): AuditRecordData
    {
        $record = AuditRecord::query()->createOrFirst(
            ['event_id' => $entry->eventId],
            [
                'actor_id' => $entry->actorId,
                'action' => $entry->action,
                'subject_type' => $entry->subjectType,
                'subject_id' => $entry->subjectId,
                'module' => $entry->module,
                'project_id' => null,
                'tenant_id' => null,
                'correlation_id' => $entry->correlationId,
                'reason' => $entry->reason,
                'metadata' => $entry->metadata,
                'created_at' => $entry->occurredAt,
            ],
        );

        return $this->toData($record->loadMissing('actor'));
    }

    public function paginate(AuditLogFilter $filter, string $actorId, bool $viewAll): AuditLogPage
    {
        $query = $this->visibleQuery($actorId, $viewAll)->with('actor');

        $query
            ->when($filter->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhere('subject_id', 'like', "%{$search}%")
                        ->orWhere('correlation_id', 'like', "%{$search}%");
                });
            })
            ->when($filter->module, fn (Builder $query, string $module): Builder => $query->where('module', $module))
            ->when($filter->action, fn (Builder $query, string $action): Builder => $query->where('action', $action))
            ->when($filter->dateFrom, fn (Builder $query, DateTimeImmutable $date): Builder => $query->where('created_at', '>=', $date))
            ->when($filter->dateTo, fn (Builder $query, DateTimeImmutable $date): Builder => $query->where('created_at', '<=', $date));

        $direction = $filter->sortDirection === 'asc' ? 'asc' : 'desc';
        $paginator = $query->orderBy('created_at', $direction)->orderBy('id', $direction)->paginate(
            perPage: $filter->perPage,
            page: $filter->page,
        );

        return new AuditLogPage(
            items: array_values(array_map(
                fn (AuditRecord $record): AuditRecordData => $this->toData($record),
                $paginator->items(),
            )),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
        );
    }

    public function findVisible(string $auditLogId, string $actorId, bool $viewAll): ?AuditRecordData
    {
        $record = $this->visibleQuery($actorId, $viewAll)
            ->with('actor')
            ->find($auditLogId);

        return $record instanceof AuditRecord ? $this->toData($record) : null;
    }

    /** @return Builder<AuditRecord> */
    private function visibleQuery(string $actorId, bool $viewAll): Builder
    {
        return AuditRecord::query()
            ->when(! $viewAll, fn (Builder $query): Builder => $query->where('actor_id', $actorId));
    }

    private function toData(AuditRecord $record): AuditRecordData
    {
        return new AuditRecordData(
            id: $record->id,
            eventId: $record->event_id,
            actorId: $record->actor_id,
            actorName: $record->actor?->name,
            action: $record->action,
            subjectType: $record->subject_type,
            subjectId: $record->subject_id,
            module: $record->module,
            correlationId: $record->correlation_id,
            reason: $record->reason,
            metadata: $record->metadata,
            createdAt: DateTimeImmutable::createFromInterface($record->created_at),
        );
    }
}
