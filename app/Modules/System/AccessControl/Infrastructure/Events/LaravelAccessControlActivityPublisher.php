<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Events;

use App\Modules\System\AccessControl\Application\Contracts\AccessControlActivityPublisher;
use App\Modules\System\AccessControl\Application\Events\AccessControlActivityOccurred;
use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

final readonly class LaravelAccessControlActivityPublisher implements AccessControlActivityPublisher
{
    public function __construct(
        private DatabaseManager $database,
        private Dispatcher $events,
    ) {}

    public function publish(
        ?string $actorId,
        string $action,
        string $subjectType,
        Closure $mutation,
        Closure $subjectId,
        Closure $metadata,
        ?string $reason = null,
        ?string $correlationId = null,
    ): mixed {
        return $this->database->connection()->transaction(function () use (
            $actorId,
            $action,
            $subjectType,
            $mutation,
            $subjectId,
            $metadata,
            $reason,
            $correlationId,
        ): mixed {
            $result = $mutation();

            $this->events->dispatch(new AccessControlActivityOccurred(
                eventName: 'access-control.activity.occurred',
                version: 1,
                eventId: (string) Str::ulid(),
                occurredAt: now()->toIso8601String(),
                correlationId: $correlationId ?? (string) Str::ulid(),
                actorId: $actorId,
                action: $action,
                subjectType: $subjectType,
                subjectId: $subjectId($result),
                reason: $reason,
                metadata: $metadata($result),
            ));

            return $result;
        });
    }
}
