<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

final readonly class ImpersonationStateData
{
    public function __construct(
        public bool $active,
        public string $actorName,
        public string $targetName,
    ) {}

    /** @return array{active: bool, actor_name: string, target_name: string} */
    public function toArray(): array
    {
        return [
            'active' => $this->active,
            'actor_name' => $this->actorName,
            'target_name' => $this->targetName,
        ];
    }
}
