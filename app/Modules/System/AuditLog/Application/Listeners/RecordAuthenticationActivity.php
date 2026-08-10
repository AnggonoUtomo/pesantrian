<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Application\Listeners;

use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

final readonly class RecordAuthenticationActivity
{
    public function __construct(private AuditRecorder $recorder) {}

    public function handleLogin(Login $event): void
    {
        $this->record($event->user, 'authentication.signed_in');
    }

    public function handleLogout(Logout $event): void
    {
        $this->record($event->user, 'authentication.signed_out');
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->record($event->user, 'authentication.password_reset');
    }

    public function handleVerified(Verified $event): void
    {
        if (! $event->user instanceof Authenticatable) {
            return;
        }

        $this->record($event->user, 'authentication.email_verified');
    }

    private function record(Authenticatable $user, string $action): void
    {
        $this->recorder->record(new AuditEntryData(
            eventId: (string) Str::ulid(),
            actorId: (string) $user->getAuthIdentifier(),
            action: $action,
            subjectType: 'account',
            subjectId: (string) $user->getAuthIdentifier(),
            module: 'Authentication',
            correlationId: (string) Str::ulid(),
            reason: null,
            metadata: [],
            occurredAt: now()->toDateTimeImmutable(),
        ));
    }
}
