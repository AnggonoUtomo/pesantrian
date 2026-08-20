<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Contracts;

use App\Modules\System\AuditLog\Application\DTO\AuditPaginationSettings;

interface AuditRuntimeSettings
{
    public function pagination(): AuditPaginationSettings;
}
