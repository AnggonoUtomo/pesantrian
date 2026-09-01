<?php

return [
    [
        'key' => 'santri.view',
        'description' => 'Melihat data induk santri dan wali.',
        'module' => 'Santri',
        'sensitive' => false,
    ],
    [
        'key' => 'santri.manage',
        'description' => 'Mengelola data induk santri dan wali.',
        'module' => 'Santri',
        'sensitive' => true,
    ],
    [
        'key' => 'santri.lifecycle',
        'description' => 'Mengelola status lifecycle santri.',
        'module' => 'Santri',
        'sensitive' => true,
    ],
    [
        'key' => 'santri.archive',
        'description' => 'Mengarsipkan dan memulihkan data santri.',
        'module' => 'Santri',
        'sensitive' => true,
    ],
];
