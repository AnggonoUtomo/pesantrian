<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Infrastructure\Authentication;

use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationEnded;
use App\Modules\System\UserManagement\Domain\Events\UserImpersonationStarted;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

final readonly class LaravelImpersonationSession implements ImpersonationSession
{
    public function __construct(
        private Request $request,
        private AuthFactory $auth,
    ) {}

    public function start(Authenticatable $actor, string $targetUserId, string $reason): void
    {
        $startedAt = now()->toIso8601String();
        $session = $this->session();

        $session->put([
            'impersonation.actor_id' => (string) $actor->getAuthIdentifier(),
            'impersonation.target_id' => $targetUserId,
            'impersonation.started_at' => $startedAt,
            'impersonation.reason' => $reason,
        ]);

        $this->auth->guard('web')->loginUsingId($targetUserId);
        $session->regenerate();

        event(new UserImpersonationStarted(
            actorId: (string) $actor->getAuthIdentifier(),
            targetId: $targetUserId,
            reason: $reason,
            startedAt: $startedAt,
        ));
    }

    public function leave(Authenticatable $actor): void
    {
        $session = $this->session();
        $actorId = $session->get('impersonation.actor_id');
        $targetId = $session->get('impersonation.target_id');
        $startedAt = $session->get('impersonation.started_at');
        $reason = $session->get('impersonation.reason');

        if (! is_string($actorId) || ! is_string($targetId)
            || ! is_string($startedAt) || ! is_string($reason)) {
            return;
        }

        $this->auth->guard('web')->loginUsingId($actorId);
        $session->forget([
            'impersonation.actor_id',
            'impersonation.target_id',
            'impersonation.started_at',
            'impersonation.reason',
        ]);
        $session->regenerate();

        event(new UserImpersonationEnded(
            actorId: $actorId,
            targetId: $targetId,
            reason: $reason,
            startedAt: $startedAt,
            endedAt: now()->toIso8601String(),
        ));
    }

    private function session(): Session
    {
        return $this->request->session();
    }
}
