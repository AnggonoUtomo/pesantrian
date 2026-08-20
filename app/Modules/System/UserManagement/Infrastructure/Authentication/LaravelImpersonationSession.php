<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Infrastructure\Authentication;

use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationEnded;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationStarted;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

final readonly class LaravelImpersonationSession implements ImpersonationSession
{
    public function __construct(
        private Request $request,
        private AuthFactory $auth,
        private UserManagementActivityPublisher $activities,
    ) {}

    public function start(
        Authenticatable $actor,
        string $targetUserId,
        string $reason,
        ?string $correlationId = null,
    ): void {
        $startedAt = now()->toIso8601String();
        $correlationId ??= (string) Str::ulid();
        $session = $this->session();
        $actorId = (string) $actor->getAuthIdentifier();

        try {
            $this->activities->publish(
                actorId: $actorId,
                action: 'user.impersonation_started',
                subjectType: 'user',
                mutation: function () use (
                    $session,
                    $actorId,
                    $targetUserId,
                    $startedAt,
                    $reason,
                    $correlationId,
                ): string {
                    $session->put([
                        'impersonation.actor_id' => $actorId,
                        'impersonation.target_id' => $targetUserId,
                        'impersonation.started_at' => $startedAt,
                        'impersonation.reason' => $reason,
                        'impersonation.correlation_id' => $correlationId,
                    ]);

                    $this->auth->guard('web')->loginUsingId($targetUserId);
                    $session->regenerate();

                    event(new UserImpersonationStarted(
                        actorId: $actorId,
                        targetId: $targetUserId,
                        reason: $reason,
                        startedAt: $startedAt,
                        correlationId: $correlationId,
                    ));

                    return $targetUserId;
                },
                subjectId: static fn (string $targetId): string => $targetId,
                metadata: static fn (string $targetId): array => ['result' => 'started'],
                reason: $reason,
                correlationId: $correlationId,
            );
        } catch (Throwable $exception) {
            $this->auth->guard('web')->loginUsingId($actorId);
            $this->forgetImpersonation($session);
            $session->regenerate();

            throw $exception;
        }
    }

    public function leave(Authenticatable $actor): void
    {
        $session = $this->session();
        $actorId = $session->get('impersonation.actor_id');
        $targetId = $session->get('impersonation.target_id');
        $startedAt = $session->get('impersonation.started_at');
        $reason = $session->get('impersonation.reason');
        $correlationId = $session->get('impersonation.correlation_id');

        if (! is_string($actorId) || ! is_string($targetId)
            || ! is_string($startedAt) || ! is_string($reason)
            || ! is_string($correlationId)) {
            return;
        }

        try {
            $this->activities->publish(
                actorId: $actorId,
                action: 'user.impersonation_ended',
                subjectType: 'user',
                mutation: function () use (
                    $session,
                    $actorId,
                    $targetId,
                    $reason,
                    $startedAt,
                    $correlationId,
                ): string {
                    $this->auth->guard('web')->loginUsingId($actorId);
                    $this->forgetImpersonation($session);
                    $session->regenerate();

                    event(new UserImpersonationEnded(
                        actorId: $actorId,
                        targetId: $targetId,
                        reason: $reason,
                        startedAt: $startedAt,
                        endedAt: now()->toIso8601String(),
                        correlationId: $correlationId,
                    ));

                    return $targetId;
                },
                subjectId: static fn (string $endedTargetId): string => $endedTargetId,
                metadata: static fn (string $endedTargetId): array => ['result' => 'ended'],
                reason: $reason,
                correlationId: $correlationId,
            );
        } catch (Throwable $exception) {
            $this->auth->guard('web')->loginUsingId($targetId);
            $session->put([
                'impersonation.actor_id' => $actorId,
                'impersonation.target_id' => $targetId,
                'impersonation.started_at' => $startedAt,
                'impersonation.reason' => $reason,
                'impersonation.correlation_id' => $correlationId,
            ]);
            $session->regenerate();

            throw $exception;
        }
    }

    public function active(): bool
    {
        $session = $this->session();

        return is_string($session->get('impersonation.actor_id'))
            && is_string($session->get('impersonation.target_id'))
            && is_string($session->get('impersonation.started_at'))
            && is_string($session->get('impersonation.reason'))
            && is_string($session->get('impersonation.correlation_id'));
    }

    private function session(): Session
    {
        return $this->request->session();
    }

    private function forgetImpersonation(Session $session): void
    {
        $session->forget([
            'impersonation.actor_id',
            'impersonation.target_id',
            'impersonation.started_at',
            'impersonation.reason',
            'impersonation.correlation_id',
        ]);
    }
}
