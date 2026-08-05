<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use DomainException;

final class ProtectedUserMutation extends DomainException
{
    public function __construct()
    {
        parent::__construct('User SuperSystem tidak boleh diubah atau dihapus.');
    }
}
