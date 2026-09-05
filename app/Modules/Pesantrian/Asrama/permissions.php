<?php

return [
    [
        'key' => 'asrama.view',
        'description' => 'Melihat data asrama, kamar, kapasitas, dan keterisian.',
        'module' => 'Asrama',
        'sensitive' => false,
    ],
    [
        'key' => 'asrama.manage',
        'description' => 'Mengelola data asrama dan kamar.',
        'module' => 'Asrama',
        'sensitive' => true,
    ],
    [
        'key' => 'asrama.placement',
        'description' => 'Mengelola penempatan, perpindahan, dan keluar kamar santri.',
        'module' => 'Asrama',
        'sensitive' => true,
    ],
    [
        'key' => 'asrama.supervisor',
        'description' => 'Mengelola penugasan musyrif atau pembina asrama.',
        'module' => 'Asrama',
        'sensitive' => true,
    ],
    [
        'key' => 'asrama.archive',
        'description' => 'Mengarsipkan dan memulihkan data asrama atau kamar.',
        'module' => 'Asrama',
        'sensitive' => true,
    ],
];
