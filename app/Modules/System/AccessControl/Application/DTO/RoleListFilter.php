<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

use InvalidArgumentException;

final readonly class RoleListFilter
{
    /** @param 'asc'|'desc' $sortDirection */
    private function __construct(
        public ?string $search,
        public int $page,
        public int $perPage,
        public string $sortDirection,
    ) {}

    public static function from(
        ?string $search,
        ?int $page,
        ?int $perPage,
        ?string $sort,
    ): self {
        $search = trim((string) $search);
        $page ??= 1;
        $perPage ??= 25;
        $sort = trim((string) $sort);

        if (mb_strlen($search) > 100
            || $page < 1
            || $perPage < 1
            || $perPage > 100
            || ! in_array($sort, ['', 'name', '-name'], true)) {
            throw new InvalidArgumentException('Filter role tidak valid.');
        }

        return new self(
            search: $search === '' ? null : $search,
            page: $page,
            perPage: $perPage,
            sortDirection: $sort === '-name' ? 'desc' : 'asc',
        );
    }
}
