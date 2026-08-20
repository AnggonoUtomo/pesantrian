<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

final readonly class InvitationMailSettings
{
    public function __construct(
        public string $mailer,
        public ?string $host,
        public int $port,
        public ?string $username,
        public ?string $password,
        public string $fromAddress,
        public string $fromName,
        public int $resetExpireMinutes,
    ) {}
}
