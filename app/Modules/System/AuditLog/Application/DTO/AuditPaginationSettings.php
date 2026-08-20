<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\DTO;

final readonly class AuditPaginationSettings
{
    /** @param list<int> $perPageOptions */
    public function __construct(
        public array $perPageOptions,
        public int $defaultPerPage,
    ) {}

    /** @return array{perPageOptions: list<int>, defaultPerPage: int} */
    public function toArray(): array
    {
        return [
            'perPageOptions' => $this->perPageOptions,
            'defaultPerPage' => $this->defaultPerPage,
        ];
    }
}
