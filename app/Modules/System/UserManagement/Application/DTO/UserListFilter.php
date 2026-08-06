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
    ) {}

    public static function from(
        ?string $search,
        ?string $status = null,
        ?string $role = null,
        ?string $archive = null,
    ): self
    {
        $search = trim((string) $search);
        $statusValue = trim((string) $status);
        $role = trim((string) $role);
        $archive = trim((string) $archive);

        if (mb_strlen($search) > 100 || mb_strlen($role) > 100) {
            throw new InvalidArgumentException('Pencarian user terlalu panjang.');
        }

        $status = $statusValue === '' ? null : UserStatus::tryFrom($statusValue);

        if (($statusValue !== '' && $status === null) || ! in_array($archive, ['', 'all', 'active', 'archived'], true)) {
            throw new InvalidArgumentException('Filter daftar user tidak valid.');
        }

        return new self(
            $search === '' ? null : $search,
            $status,
            $role === '' ? null : $role,
            $archive === '' ? 'all' : $archive,
        );
    }
}
