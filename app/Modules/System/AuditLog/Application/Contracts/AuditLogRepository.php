<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Contracts;

use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Application\DTO\AuditLogFilter;
use App\Modules\System\AuditLog\Application\DTO\AuditLogPage;
use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;

interface AuditLogRepository
{
    public function record(AuditEntryData $entry): AuditRecordData;

    public function paginate(AuditLogFilter $filter, string $actorId, bool $viewAll): AuditLogPage;

    public function findVisible(string $auditLogId, string $actorId, bool $viewAll): ?AuditRecordData;
}
