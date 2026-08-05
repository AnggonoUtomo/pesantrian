<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use InvalidArgumentException;

final readonly class UserListFilter
{
    private function __construct(public ?string $search) {}

    public static function from(?string $search): self
    {
        $search = trim((string) $search);

        if (mb_strlen($search) > 100) {
            throw new InvalidArgumentException('Pencarian user terlalu panjang.');
        }

        return new self($search === '' ? null : $search);
    }
}
