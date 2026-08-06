<?php

declare(strict_types=1);

return [
    [
        'key' => 'system_setting.view',
        'description' => 'Melihat konfigurasi runtime global.',
        'module' => 'SystemSetting',
        'sensitive' => true,
    ],
    [
        'key' => 'system_setting.manage',
        'description' => 'Mengubah konfigurasi runtime global.',
        'module' => 'SystemSetting',
        'sensitive' => true,
    ],
];
