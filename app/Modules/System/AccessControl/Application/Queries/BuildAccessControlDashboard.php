<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Queries;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlReadRepository;
use App\Modules\System\AccessControl\Application\DTO\AccessControlDashboardData;

final readonly class BuildAccessControlDashboard
{
    public function __construct(private AccessControlReadRepository $readRepository) {}

    public function execute(): AccessControlDashboardData
    {
        return $this->readRepository->dashboard();
    }
}
