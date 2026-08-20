<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Exceptions\InvitationDeliveryFailed;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

final readonly class InviteUser
{
    public function __construct(
        private CreateUser $createUser,
        private UserRuntimeSettings $runtimeSettings,
        private UserRepository $users,
    ) {}

    public function execute(Authenticatable $actor, string $name, string $email, UserStatus $status, ?string $role): UserData
    {
        $mail = $this->runtimeSettings->invitationMail();
        config([
            'mail.default' => $mail->mailer,
            'mail.mailers.smtp.host' => $mail->host,
            'mail.mailers.smtp.port' => $mail->port,
            'mail.mailers.smtp.username' => $mail->username,
            'mail.mailers.smtp.password' => $mail->password,
            'mail.mailers.smtp.encryption' => null,
            'mail.from.address' => $mail->fromAddress,
            'mail.from.name' => $mail->fromName,
            'auth.passwords.users.expire' => $mail->resetExpireMinutes,
        ]);
        Mail::purge();
        $user = $this->createUser->execute($actor, new CreateUserData(trim($name), trim($email), Str::password(64), $status, $role));

        try {
            $deliveryStatus = Password::sendResetLink(['email' => $user->email]);
        } catch (Throwable) {
            $this->users->forceDelete($user->id);

            throw new InvitationDeliveryFailed;
        }

        if ($deliveryStatus !== Password::RESET_LINK_SENT) {
            $this->users->forceDelete($user->id);

            throw new InvitationDeliveryFailed;
        }

        return $user;
    }
}
