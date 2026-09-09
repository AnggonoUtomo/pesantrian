<?php

return [
    [
        'key' => 'prestasi_santri.view',
        'description' => 'Melihat daftar, detail, status, dan histori prestasi santri.',
        'module' => 'PrestasiSantri',
        'sensitive' => false,
    ],
    [
        'key' => 'prestasi_santri.manage',
        'description' => 'Mengelola kategori prestasi santri.',
        'module' => 'PrestasiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'prestasi_santri.record',
        'description' => 'Mencatat, memperbarui, dan mengajukan catatan prestasi santri.',
        'module' => 'PrestasiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'prestasi_santri.verify',
        'description' => 'Memverifikasi atau meminta revisi catatan prestasi santri.',
        'module' => 'PrestasiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'prestasi_santri.archive',
        'description' => 'Mengarsipkan kategori atau membatalkan catatan prestasi yang salah dibuat.',
        'module' => 'PrestasiSantri',
        'sensitive' => true,
    ],
];
