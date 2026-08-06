<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\DTO;

use DateTimeImmutable;

final readonly class AuditLogFilter
{
    public function __construct(
        public ?string $search = null,
        public ?string $module = null,
        public ?string $action = null,
        public ?DateTimeImmutable $dateFrom = null,
        public ?DateTimeImmutable $dateTo = null,
        public int $page = 1,
        public int $perPage = 25,
    ) {}
}
