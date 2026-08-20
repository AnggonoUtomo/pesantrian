<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\DTO;

use InvalidArgumentException;

final readonly class PermissionListFilter
{
    /** @param 'asc'|'desc' $sortDirection */
    private function __construct(
        public ?string $search,
        public ?string $module,
        public int $page,
        public int $perPage,
        public string $sortDirection,
    ) {}

    public static function from(
        ?string $search,
        ?string $module,
        ?int $page,
        ?int $perPage,
        ?string $sort,
    ): self {
        $search = trim((string) $search);
        $module = trim((string) $module);
        $page ??= 1;
        $perPage ??= 25;
        $sort = trim((string) $sort);

        if (mb_strlen($search) > 100
            || mb_strlen($module) > 100
            || ($module !== '' && preg_match('/^[a-z0-9_]+$/', $module) !== 1)
            || $page < 1
            || $perPage < 1
            || $perPage > 100
            || ! in_array($sort, ['', 'name', '-name'], true)) {
            throw new InvalidArgumentException('Filter permission tidak valid.');
        }

        return new self(
            search: $search === '' ? null : $search,
            module: $module === '' ? null : $module,
            page: $page,
            perPage: $perPage,
            sortDirection: $sort === '-name' ? 'desc' : 'asc',
        );
    }
}
