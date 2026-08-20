<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use InvalidArgumentException;

final readonly class UserListFilter
{
    private function __construct(
        public ?string $search,
        public ?UserStatus $status,
        public ?string $role,
        public string $archive,
        public int $page,
        public int $perPage,
        public string $sortBy,
        public string $sortDirection,
    ) {}

    /** @param list<int> $perPageOptions */
    public static function from(
        ?string $search,
        ?string $status = null,
        ?string $role = null,
        ?string $archive = null,
        ?int $page = null,
        ?int $perPage = null,
        int $defaultPerPage = 25,
        array $perPageOptions = [10, 25, 50, 100],
        ?string $sortDirection = null,
        ?string $sortBy = null,
    ): self {
        $search = trim((string) $search);
        $statusValue = trim((string) $status);
        $role = trim((string) $role);
        $archive = trim((string) $archive);
        $sortDirection = strtolower(trim((string) $sortDirection));
        $sortBy = strtolower(trim((string) $sortBy));

        if (mb_strlen($search) > 100 || mb_strlen($role) > 100) {
            throw new InvalidArgumentException('Pencarian user terlalu panjang.');
        }

        $status = $statusValue === '' ? null : UserStatus::tryFrom($statusValue);

        if (($statusValue !== '' && $status === null)
            || ! in_array($archive, ['', 'all', 'active', 'archived'], true)
            || ! in_array($sortDirection, ['', 'asc', 'desc'], true)
            || ! in_array($sortBy, ['', 'created_at', 'name'], true)) {
            throw new InvalidArgumentException('Filter daftar user tidak valid.');
        }

        $page ??= 1;
        $perPage ??= $defaultPerPage;

        if ($page < 1 || ! in_array($perPage, $perPageOptions, true)) {
            throw new InvalidArgumentException('Pagination daftar user tidak valid.');
        }

        return new self(
            $search === '' ? null : $search,
            $status,
            $role === '' ? null : $role,
            $archive === '' ? 'all' : $archive,
            $page,
            $perPage,
            $sortBy === '' ? 'created_at' : $sortBy,
            $sortDirection === '' ? 'desc' : $sortDirection,
        );
    }
}
