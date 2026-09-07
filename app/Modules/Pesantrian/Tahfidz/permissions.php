<?php

return [
    [
        'key' => 'tahfidz.view',
        'description' => 'Melihat program, target, setoran, murojaah, dan progres hafalan santri.',
        'module' => 'Tahfidz',
        'sensitive' => false,
    ],
    [
        'key' => 'tahfidz.manage',
        'description' => 'Mengelola program dan target hafalan santri.',
        'module' => 'Tahfidz',
        'sensitive' => true,
    ],
    [
        'key' => 'tahfidz.record',
        'description' => 'Mencatat setoran hafalan baru dan murojaah santri.',
        'module' => 'Tahfidz',
        'sensitive' => true,
    ],
    [
        'key' => 'tahfidz.review',
        'description' => 'Mereview hasil setoran hafalan santri.',
        'module' => 'Tahfidz',
        'sensitive' => true,
    ],
    [
        'key' => 'tahfidz.archive',
        'description' => 'Mengarsipkan atau membatalkan program, target, atau setoran tahfidz yang salah dibuat.',
        'module' => 'Tahfidz',
        'sensitive' => true,
    ],
];
