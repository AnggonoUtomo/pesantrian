<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use InvalidArgumentException;

final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserStatus $status = UserStatus::ACTIVE,
        public ?string $role = null,
    ) {
        self::assertName($name);
        self::assertEmail($email);
        self::assertPassword($password);

        if ($role !== null && trim($role) === '') {
            throw new InvalidArgumentException('Role user tidak valid.');
        }
    }

    private static function assertName(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Nama user wajib diisi.');
        }
    }

    private static function assertEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Email user tidak valid.');
        }
    }

    private static function assertPassword(string $password): void
    {
        if (mb_strlen($password) < 8) {
            throw new InvalidArgumentException('Password user minimal 8 karakter.');
        }
    }
}
