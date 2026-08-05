<?php

return [
    [
        'key' => 'user.view',
        'description' => 'Melihat daftar dan detail user.',
        'module' => 'UserManagement',
        'sensitive' => false,
    ],
    [
        'key' => 'user.create',
        'description' => 'Membuat user baru.',
        'module' => 'UserManagement',
        'sensitive' => true,
    ],
    [
        'key' => 'user.update',
        'description' => 'Memperbarui data user.',
        'module' => 'UserManagement',
        'sensitive' => true,
    ],
    [
        'key' => 'user.status.manage',
        'description' => 'Mengubah status lifecycle user.',
        'module' => 'UserManagement',
        'sensitive' => true,
    ],
    [
        'key' => 'user.delete',
        'description' => 'Melakukan soft delete user.',
        'module' => 'UserManagement',
        'sensitive' => true,
    ],
    [
        'key' => 'user.impersonate',
        'description' => 'Memulai impersonation user dengan reason.',
        'module' => 'UserManagement',
        'sensitive' => true,
    ],
];
