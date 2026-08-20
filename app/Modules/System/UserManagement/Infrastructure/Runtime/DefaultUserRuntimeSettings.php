<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Infrastructure\Runtime;

use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;
use App\Modules\System\UserManagement\Application\DTO\InvitationMailSettings;
use App\Modules\System\UserManagement\Application\DTO\UserPaginationSettings;
use Illuminate\Contracts\Config\Repository;

final readonly class DefaultUserRuntimeSettings implements UserRuntimeSettings
{
    public function __construct(private Repository $config) {}

    public function pagination(): UserPaginationSettings
    {
        $options = $this->positiveIntegerList(
            $this->config->get('user-management.pagination.per_page_options'),
            [5, 10, 25, 50, 100],
        );
        $default = $this->positiveInteger(
            $this->config->get('user-management.pagination.default_per_page'),
            25,
        );

        return new UserPaginationSettings(
            perPageOptions: $options,
            defaultPerPage: in_array($default, $options, true) ? $default : $options[0],
        );
    }

    public function invitationMail(): InvitationMailSettings
    {
        return new InvitationMailSettings(
            mailer: $this->string('mail.default', 'smtp'),
            host: $this->nullableString('mail.mailers.smtp.host'),
            port: $this->positiveInteger($this->config->get('mail.mailers.smtp.port'), 1025),
            username: $this->nullableString('mail.mailers.smtp.username'),
            password: $this->nullableString('mail.mailers.smtp.password'),
            fromAddress: $this->string('mail.from.address', 'noreply@example.test'),
            fromName: $this->string('mail.from.name', 'Laravel'),
            resetExpireMinutes: $this->positiveInteger(
                $this->config->get('user-management.invitation.reset_expire_minutes'),
                60,
            ),
        );
    }

    private function string(string $key, string $fallback): string
    {
        $value = $this->config->get($key);

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->config->get($key);

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function positiveInteger(mixed $value, int $fallback): int
    {
        return is_int($value) && $value > 0 ? $value : $fallback;
    }

    /**
     * @param  list<int>  $fallback
     * @return list<int>
     */
    private function positiveIntegerList(mixed $value, array $fallback): array
    {
        if (! is_array($value)) {
            return $fallback;
        }

        $items = array_values(array_unique(array_filter(
            $value,
            static fn (mixed $item): bool => is_int($item) && $item > 0,
        )));

        return $items === [] ? $fallback : $items;
    }
}
