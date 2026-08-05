<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Entities;

use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use InvalidArgumentException;

final class UserLifecycle
{
    private function __construct(
        public readonly string $userId,
        public UserStatus $status,
        private bool $protectedUser,
        public bool $isDeleted = false,
    ) {}

    public static function for(string $userId, UserStatus $status): self
    {
        return new self($userId, $status, false);
    }

    public static function forProtectedUser(string $userId): self
    {
        return new self($userId, UserStatus::ACTIVE, true);
    }

    public function changeStatus(UserStatus $status): void
    {
        $this->guardMutation();

        if ($this->status === $status) {
            throw new InvalidArgumentException('Status user harus berbeda dari status saat ini.');
        }

        $this->status = $status;
    }

    public function softDelete(): void
    {
        $this->guardMutation();

        $this->isDeleted = true;
    }

    private function guardMutation(): void
    {
        if ($this->protectedUser) {
            throw new ProtectedUserMutation;
        }
    }
}
