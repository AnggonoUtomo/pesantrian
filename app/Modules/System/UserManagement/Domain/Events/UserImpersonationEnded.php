<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Events;

final readonly class UserImpersonationEnded
{
    public function __construct(
        public string $actorId,
        public string $targetId,
        public string $reason,
        public string $startedAt,
        public string $endedAt,
    ) {}
}
