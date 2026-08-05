<?php

return [
    [
        'key' => 'system.dashboard.view',
        'description' => 'Melihat dashboard area System.',
        'module' => 'System',
        'sensitive' => false,
    ],
    [
        'key' => 'access_control.role.manage',
        'description' => 'Mengelola role AccessControl.',
        'module' => 'AccessControl',
        'sensitive' => false,
    ],
    [
        'key' => 'access_control.permission.manage',
        'description' => 'Mengelola permission AccessControl.',
        'module' => 'AccessControl',
        'sensitive' => true,
    ],
    [
        'key' => 'access_control.role.assign',
        'description' => 'Memberikan atau mencabut role dari user.',
        'module' => 'AccessControl',
        'sensitive' => true,
    ],
    [
        'key' => 'access_control.permission.assign',
        'description' => 'Memberikan atau mencabut direct permission dari user.',
        'module' => 'AccessControl',
        'sensitive' => true,
    ],
];
