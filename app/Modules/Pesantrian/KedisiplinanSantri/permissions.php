<?php

return [
    [
        'key' => 'kedisiplinan_santri.view',
        'description' => 'Melihat daftar, detail, status, dan histori pelanggaran/kedisiplinan santri.',
        'module' => 'KedisiplinanSantri',
        'sensitive' => false,
    ],
    [
        'key' => 'kedisiplinan_santri.manage',
        'description' => 'Membuat dan memperbarui catatan pelanggaran/kedisiplinan santri.',
        'module' => 'KedisiplinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'kedisiplinan_santri.review',
        'description' => 'Mereview catatan pelanggaran dan menetapkan tindakan pembinaan santri.',
        'module' => 'KedisiplinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'kedisiplinan_santri.resolve',
        'description' => 'Menyelesaikan kasus kedisiplinan santri dengan catatan penyelesaian.',
        'module' => 'KedisiplinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'kedisiplinan_santri.archive',
        'description' => 'Membatalkan atau mengarsipkan catatan kedisiplinan yang salah dibuat.',
        'module' => 'KedisiplinanSantri',
        'sensitive' => true,
    ],
];
