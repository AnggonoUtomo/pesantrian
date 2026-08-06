<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Queries;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AuditLog\Application\Contracts\AuditLogRepository;
use App\Modules\System\AuditLog\Application\DTO\AuditLogFilter;
use App\Modules\System\AuditLog\Application\DTO\AuditLogPage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ListAuditLogs
{
    public function __construct(
        private AuthorizationCapability $authorization,
        private AuditLogRepository $repository,
    ) {}

    public function execute(Authenticatable $actor, AuditLogFilter $filter): AuditLogPage
    {
        if (! $this->authorization->can($actor, 'audit_log.view')->allowed) {
            throw new AuthorizationException('Audit log tidak dapat diakses.');
        }

        return $this->repository->paginate(
            filter: $filter,
            actorId: (string) $actor->getAuthIdentifier(),
            viewAll: $this->authorization->isSuperSystem($actor),
        );
    }
}
