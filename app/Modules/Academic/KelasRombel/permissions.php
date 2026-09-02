<?php

return [
    [
        'key' => 'kelas_rombel.view',
        'description' => 'Melihat data kelas, rombel, kurikulum, dan penempatan santri.',
        'module' => 'KelasRombel',
        'sensitive' => false,
    ],
    [
        'key' => 'kelas_rombel.manage',
        'description' => 'Mengelola data kurikulum, kelas, dan rombel.',
        'module' => 'KelasRombel',
        'sensitive' => true,
    ],
    [
        'key' => 'kelas_rombel.placement',
        'description' => 'Mengelola penempatan dan perpindahan santri antar rombel.',
        'module' => 'KelasRombel',
        'sensitive' => true,
    ],
    [
        'key' => 'kelas_rombel.archive',
        'description' => 'Mengarsipkan dan memulihkan data rombel.',
        'module' => 'KelasRombel',
        'sensitive' => true,
    ],
];
