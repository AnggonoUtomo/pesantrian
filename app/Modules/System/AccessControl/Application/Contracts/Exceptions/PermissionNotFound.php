<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts\Exceptions;

use RuntimeException;

final class PermissionNotFound extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Permission tidak ditemukan.');
    }
}
