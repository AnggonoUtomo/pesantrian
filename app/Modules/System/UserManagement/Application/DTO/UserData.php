<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;

final readonly class UserData
{
    /** @param list<string> $roles */
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public UserStatus $status,
        public bool $isProtected,
        public ?string $deletedAt,
        public array $roles = [],
        public ?string $avatarUrl = null,
        public bool $emailVerified = false,
        public ?string $lastLoginAt = null,
    ) {}
}
