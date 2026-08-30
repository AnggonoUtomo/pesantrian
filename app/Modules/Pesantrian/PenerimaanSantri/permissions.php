<?php

return [
    [
        'key' => 'penerimaan_santri.view',
        'description' => 'Melihat data PPDB / penerimaan santri baru.',
        'module' => 'PenerimaanSantri',
        'sensitive' => false,
    ],
    [
        'key' => 'penerimaan_santri.manage',
        'description' => 'Mengelola data PPDB / penerimaan santri baru.',
        'module' => 'PenerimaanSantri',
        'sensitive' => true,
    ],
    [
        'key' => 'penerimaan_santri.decide',
        'description' => 'Memverifikasi dan memutuskan status pendaftaran santri baru.',
        'module' => 'PenerimaanSantri',
        'sensitive' => true,
    ],
];
