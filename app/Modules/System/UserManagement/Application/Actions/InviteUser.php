<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class InviteUser
{
    public function __construct(private CreateUser $createUser) {}

    public function execute(Authenticatable $actor, string $name, string $email, ?string $role): UserData
    {
        $user = $this->createUser->execute($actor, new CreateUserData(trim($name), trim($email), Str::password(64), UserStatus::ACTIVE, $role));
        if (Password::sendResetLink(['email' => $user->email]) !== Password::RESET_LINK_SENT) {
            throw new RuntimeException('Email undangan tidak dapat dikirim.');
        }
        return $user;
    }
}
