<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Infrastructure\Context;

use Illuminate\Http\Request;

final readonly class AuditSecurityContext
{
    /** @var list<string> */
    private const ACTIONS = [
        'access_control.role.created',
        'access_control.role.deleted',
        'access_control.role.permissions_synced',
        'authentication.email_verified',
        'authentication.password_reset',
        'authentication.signed_in',
        'authentication.signed_out',
        'user.impersonation_ended',
        'user.impersonation_started',
        'user.role_assigned',
        'user.status_changed',
    ];

    public function __construct(private Request $request) {}

    /** @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public function merge(string $action, array $metadata): array
    {
        if (! $this->recordsContextFor($action)) {
            unset($metadata['browser'], $metadata['ip_address']);

            return $metadata;
        }

        return array_filter([
            ...$metadata,
            'browser' => $this->browser(),
            'ip_address' => $this->ipAddress(),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function recordsContextFor(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    private function browser(): ?string
    {
        $userAgent = $this->request->userAgent();

        if (! is_string($userAgent) || $userAgent === '') {
            return null;
        }

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => null,
        };
        $platform = match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => null,
        };

        return $browser === null ? null : ($platform === null ? $browser : sprintf('%s di %s', $browser, $platform));
    }

    private function ipAddress(): ?string
    {
        $ipAddress = $this->request->ip();

        return is_string($ipAddress) && filter_var($ipAddress, FILTER_VALIDATE_IP) !== false
            ? $ipAddress
            : null;
    }
}
