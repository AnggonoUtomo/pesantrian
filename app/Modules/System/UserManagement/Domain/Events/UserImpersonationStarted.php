<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Events;

final readonly class UserImpersonationStarted
{
    public function __construct(
        public string $actorId,
        public string $targetId,
        public string $reason,
        public string $startedAt,
        public string $correlationId,
    ) {}
}
