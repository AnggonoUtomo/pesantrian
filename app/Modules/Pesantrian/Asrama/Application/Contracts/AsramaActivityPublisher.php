<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use Closure;

interface AsramaActivityPublisher
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
