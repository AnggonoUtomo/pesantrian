<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Resources;

use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;

final readonly class AuditLogResource
{
    public function __construct(private AuditRecordData $record) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->record->toArray();
    }
}
