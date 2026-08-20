<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Queries;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\DTO\RoleData;

final readonly class GetRole
{
    public function __construct(private AccessControlReadRepository $repository) {}

    public function execute(string $roleId): ?RoleData
    {
        return $this->repository->findRole($roleId);
    }
}
