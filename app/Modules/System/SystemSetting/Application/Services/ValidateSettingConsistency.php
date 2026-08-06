<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use InvalidArgumentException;

final readonly class ValidateSettingConsistency
{
    public function __construct(private SystemSettingReader $settings) {}

    public function forUpdate(string $key, int|bool|string|null $value): void
    {
        if (! in_array($key, [
            'security.session.idle_minutes',
            'security.session.absolute_hours',
        ], true)) {
            return;
        }

        $idleMinutes = $key === 'security.session.idle_minutes'
            ? (int) $value
            : $this->settings->integer('security.session.idle_minutes');
        $absoluteHours = $key === 'security.session.absolute_hours'
            ? (int) $value
            : $this->settings->integer('security.session.absolute_hours');

        if ($idleMinutes >= $absoluteHours * 60) {
            throw new InvalidArgumentException('Idle session harus lebih kecil dari absolute lifetime.');
        }
    }
}
