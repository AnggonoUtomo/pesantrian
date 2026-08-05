<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use InvalidArgumentException;

final readonly class UpdateUserData
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Nama user wajib diisi.');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email user tidak valid.');
        }
    }
}
