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
        $this->forUpdates([$key => $value]);
    }

    /** @param array<string, int|bool|string|list<int>|null> $updates */
    public function forUpdates(array $updates): void
    {
        if (array_intersect(array_keys($updates), [
            'security.session.idle_minutes',
            'security.session.absolute_hours',
        ]) !== []) {
            $idleMinutes = array_key_exists('security.session.idle_minutes', $updates)
                ? (int) $updates['security.session.idle_minutes']
                : $this->settings->integer('security.session.idle_minutes');
            $absoluteHours = array_key_exists('security.session.absolute_hours', $updates)
                ? (int) $updates['security.session.absolute_hours']
                : $this->settings->integer('security.session.absolute_hours');

            if ($idleMinutes >= $absoluteHours * 60) {
                throw new InvalidArgumentException('Idle session harus lebih kecil dari absolute lifetime.');
            }
        }

        if (array_intersect(array_keys($updates), ['pagination.per_page_options', 'pagination.default_per_page']) === []) {
            return;
        }

        $options = array_key_exists('pagination.per_page_options', $updates)
            ? $updates['pagination.per_page_options']
            : $this->settings->get('pagination.per_page_options')->value;
        $default = array_key_exists('pagination.default_per_page', $updates)
            ? (int) $updates['pagination.default_per_page']
            : $this->settings->integer('pagination.default_per_page');

        if (! is_array($options) || ! in_array($default, $options, true)) {
            throw new InvalidArgumentException('Default pagination harus tersedia pada pilihan jumlah data per halaman.');
        }
    }
}
