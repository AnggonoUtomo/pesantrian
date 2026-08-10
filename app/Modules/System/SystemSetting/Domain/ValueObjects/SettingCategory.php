<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Domain\ValueObjects;

enum SettingCategory: string
{
    case Api = 'api';
    case Password = 'password';
    case Session = 'session';
    case Mail = 'mail';
    case Pagination = 'pagination';
    case Branding = 'branding';
    case Monitoring = 'monitoring';
    case Operations = 'operations';

    public function owns(string $key): bool
    {
        return match ($this) {
            self::Password => str_starts_with($key, 'security.password.'),
            self::Session => str_starts_with($key, 'security.session.'),
            default => str_starts_with($key, $this->value.'.'),
        };
    }
}
