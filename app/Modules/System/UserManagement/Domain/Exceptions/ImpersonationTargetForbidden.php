<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use RuntimeException;

final class ImpersonationTargetForbidden extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Target impersonation tidak diizinkan.');
    }
}
