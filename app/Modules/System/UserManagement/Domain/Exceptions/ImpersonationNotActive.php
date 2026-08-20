<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use RuntimeException;

final class ImpersonationNotActive extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Session impersonation tidak aktif.');
    }
}
