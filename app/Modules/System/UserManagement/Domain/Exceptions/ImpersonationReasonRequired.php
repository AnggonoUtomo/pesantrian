<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use RuntimeException;

final class ImpersonationReasonRequired extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Alasan impersonation wajib diisi sepanjang 10 sampai 500 karakter.');
    }
}
