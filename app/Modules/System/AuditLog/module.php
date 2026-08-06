<?php

declare(strict_types=1);

return [
    'retention_days' => 365,
    'metadata' => [
        'allowed_keys' => [
            'role_name',
            'permission_keys',
            'permission_count',
            'changed_fields',
            'from_status',
            'to_status',
            'setting_key',
            'before_value',
            'after_value',
            'result',
        ],
        'sensitive_patterns' => [
            'password',
            'token',
            'secret',
            'credential',
            'authorization',
            'cookie',
            'session',
            'api_key',
        ],
        'max_depth' => 4,
        'max_items' => 50,
        'max_string_length' => 500,
        'max_reason_length' => 500,
    ],
];
