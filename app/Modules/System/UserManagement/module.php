<?php

declare(strict_types=1);

return [
    'pagination' => [
        'per_page_options' => [5, 10, 25, 50, 100],
        'default_per_page' => 25,
    ],
    'invitation' => [
        'reset_expire_minutes' => 60,
    ],
];
