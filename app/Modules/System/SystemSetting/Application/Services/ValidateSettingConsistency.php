<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use InvalidArgumentException;

final readonly class ValidateSettingConsistency
{
    public function __construct(private SystemSettingReader $settings) {}

    /** @param int|bool|string|list<int>|null $value */
    public function forUpdate(string $key, int|bool|string|array|null $value): void
    {
        if (in_array($key, [
            'security.session.idle_minutes',
            'security.session.absolute_hours',
        ], true)) {
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

        if (! in_array($key, ['pagination.per_page_options', 'pagination.default_per_page'], true)) {
            return;
        }

        $options = $key === 'pagination.per_page_options'
            ? $value
            : $this->settings->get('pagination.per_page_options')->value;
        $default = $key === 'pagination.default_per_page'
            ? (int) $value
            : $this->settings->integer('pagination.default_per_page');

        if (! is_array($options) || ! in_array($default, $options, true)) {
            throw new InvalidArgumentException('Default pagination harus tersedia pada pilihan jumlah data per halaman.');
        }
    }
}
