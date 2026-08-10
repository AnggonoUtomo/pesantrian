<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class InviteUser
{
    public function __construct(
        private CreateUser $createUser,
        private SystemSettingReader $settings,
        private UserRepository $users,
    ) {}

    public function execute(Authenticatable $actor, string $name, string $email, UserStatus $status, ?string $role): UserData
    {
        config([
            'mail.default' => $this->settings->string('mail.mailer') ?? 'smtp',
            'mail.mailers.smtp.host' => $this->settings->string('mail.host'),
            'mail.mailers.smtp.port' => $this->settings->integer('mail.port'),
            'mail.mailers.smtp.username' => $this->settings->string('mail.username'),
            'mail.mailers.smtp.password' => $this->settings->string('mail.password'),
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => $this->settings->string('mail.from_address'),
            'mail.from.name' => $this->settings->string('mail.from_name'),
        ]);
        Mail::purge();
        $user = $this->createUser->execute($actor, new CreateUserData(trim($name), trim($email), Str::password(64), $status, $role));
        if (Password::sendResetLink(['email' => $user->email]) !== Password::RESET_LINK_SENT) {
            $this->users->forceDelete($user->id);

            throw new RuntimeException('Email undangan tidak dapat dikirim.');
        }

        return $user;
    }
}
