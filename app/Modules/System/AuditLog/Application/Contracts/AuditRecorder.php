<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Contracts;

use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;

interface AuditRecorder
{
    public function record(AuditEntryData $entry): AuditRecordData;
}
