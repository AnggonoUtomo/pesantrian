<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\DTO;

final readonly class AuditLogPage
{
    /** @param list<AuditRecordData> $items */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (AuditRecordData $record): array => $record->toArray(),
                $this->items,
            ),
            'meta' => [
                'currentPage' => $this->currentPage,
                'lastPage' => $this->lastPage,
                'perPage' => $this->perPage,
                'total' => $this->total,
            ],
        ];
    }
}
