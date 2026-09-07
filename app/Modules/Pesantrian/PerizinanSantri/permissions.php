<?php

return [
    [
        'key' => 'perizinan_santri.view',
        'description' => 'Melihat daftar, detail, status, dan histori perizinan santri.',
        'module' => 'PerizinanSantri',
        'sensitive' => false,
    ],
    [
        'key' => 'perizinan_santri.manage',
        'description' => 'Membuat dan memperbarui permohonan izin santri.',
        'module' => 'PerizinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'perizinan_santri.approve',
        'description' => 'Menyetujui atau menolak permohonan izin santri.',
        'module' => 'PerizinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'perizinan_santri.checkout',
        'description' => 'Mencatat santri keluar setelah izin disetujui.',
        'module' => 'PerizinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'perizinan_santri.return',
        'description' => 'Mencatat santri kembali atau check-in dari izin.',
        'module' => 'PerizinanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'perizinan_santri.archive',
        'description' => 'Membatalkan atau mengarsipkan data izin santri yang salah dibuat.',
        'module' => 'PerizinanSantri',
        'sensitive' => true,
    ],
];
