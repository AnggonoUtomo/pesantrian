<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Runtime;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;
use App\Modules\System\UserManagement\Application\DTO\InvitationMailSettings;
use App\Modules\System\UserManagement\Application\DTO\UserPaginationSettings;

final readonly class SystemSettingUserRuntimeSettings implements UserRuntimeSettings
{
    public function __construct(
        private SystemSettingReader $settings,
        private UserPaginationSettings $paginationFallback,
        private InvitationMailSettings $invitationFallback,
    ) {}

    public function pagination(): UserPaginationSettings
    {
        $fallback = $this->paginationFallback;
        $values = $this->settings->many([
            'pagination.per_page_options',
            'pagination.default_per_page',
        ]);
        $options = $this->positiveIntegerList($values['pagination.per_page_options']->value);
        $default = $values['pagination.default_per_page']->value;

        if ($options === null) {
            return $fallback;
        }

        return new UserPaginationSettings(
            perPageOptions: $options,
            defaultPerPage: is_int($default) && in_array($default, $options, true)
                ? $default
                : $fallback->defaultPerPage,
        );
    }

    public function invitationMail(): InvitationMailSettings
    {
        $fallback = $this->invitationFallback;
        $values = $this->settings->many([
            'mail.mailer',
            'mail.host',
            'mail.port',
            'mail.username',
            'mail.password',
            'mail.from_address',
            'mail.from_name',
        ]);

        return new InvitationMailSettings(
            mailer: $this->string($values['mail.mailer']->value, $fallback->mailer),
            host: $this->nullableString($values['mail.host']->value),
            port: $this->positiveInteger($values['mail.port']->value, $fallback->port),
            username: $this->nullableString($values['mail.username']->value),
            password: $this->nullableString($values['mail.password']->value),
            fromAddress: $this->string($values['mail.from_address']->value, $fallback->fromAddress),
            fromName: $this->string($values['mail.from_name']->value, $fallback->fromName),
            resetExpireMinutes: $fallback->resetExpireMinutes,
        );
    }

    private function string(mixed $value, string $fallback): string
    {
        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function positiveInteger(mixed $value, int $fallback): int
    {
        return is_int($value) && $value > 0 ? $value : $fallback;
    }

    /** @return list<int>|null */
    private function positiveIntegerList(mixed $value): ?array
    {
        if (! is_array($value) || $value === [] || ! array_is_list($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $item) {
            if (! is_int($item) || $item <= 0) {
                return null;
            }

            $items[] = $item;
        }

        return $items;
    }
}
