<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Domain\ValueObjects;

use InvalidArgumentException;

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

    public static function fromSettingKey(string $key): self
    {
        foreach (self::cases() as $category) {
            if ($category->owns($key)) {
                return $category;
            }
        }

        throw new InvalidArgumentException("Key setting [{$key}] tidak memiliki kategori.");
    }

    public function label(): string
    {
        return match ($this) {
            self::Api => 'API',
            self::Password => 'Password',
            self::Session => 'Sesi',
            self::Mail => 'Email',
            self::Pagination => 'Pagination',
            self::Branding => 'Identitas aplikasi',
            self::Monitoring => 'Monitoring',
            self::Operations => 'Operasional',
        };
    }

    public function owns(string $key): bool
    {
        return match ($this) {
            self::Password => str_starts_with($key, 'security.password.'),
            self::Session => str_starts_with($key, 'security.session.'),
            default => str_starts_with($key, $this->value.'.'),
        };
    }
}
