<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Policies;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class AuditLogPolicy
{
    public function __construct(private AuthorizationCapability $authorization) {}

    public function viewAny(?Authenticatable $actor): bool
    {
        return $this->authorization->can($actor, 'audit_log.view')->allowed;
    }

    public function view(?Authenticatable $actor, AuditRecord $record): bool
    {
        if (! $this->authorization->can($actor, 'audit_log.view')->allowed || $actor === null) {
            return false;
        }

        return $this->authorization->isSuperSystem($actor)
            || $record->actor_id === (string) $actor->getAuthIdentifier();
    }
}
