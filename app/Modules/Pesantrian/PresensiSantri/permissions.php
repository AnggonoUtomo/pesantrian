<?php

return [
    [
        'key' => 'presensi_santri.view',
        'description' => 'Melihat sesi, detail, dan rekap presensi santri.',
        'module' => 'PresensiSantri',
        'sensitive' => false,
    ],
    [
        'key' => 'presensi_santri.manage',
        'description' => 'Membuat dan memperbarui draft sesi serta entry presensi santri.',
        'module' => 'PresensiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'presensi_santri.submit',
        'description' => 'Men-submit sesi presensi santri sebagai data operasional final.',
        'module' => 'PresensiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'presensi_santri.revise',
        'description' => 'Merevisi sesi presensi santri yang sudah disubmit.',
        'module' => 'PresensiSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'presensi_santri.archive',
        'description' => 'Membatalkan atau mengarsipkan sesi presensi santri yang salah dibuat.',
        'module' => 'PresensiSantri',
        'sensitive' => true,
    ],
];
