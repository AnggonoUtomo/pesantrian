<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

use Closure;

interface AccessControlActivityPublisher
{
    /**
     * @template TResult
     *
     * @param  Closure(): TResult  $mutation
     * @param  Closure(TResult): ?string  $subjectId
     * @param  Closure(TResult): array<string, mixed>  $metadata
     * @return TResult
     */
    public function publish(
        ?string $actorId,
        string $action,
        string $subjectType,
        Closure $mutation,
        Closure $subjectId,
        Closure $metadata,
        ?string $reason = null,
        ?string $correlationId = null,
    ): mixed;
}
