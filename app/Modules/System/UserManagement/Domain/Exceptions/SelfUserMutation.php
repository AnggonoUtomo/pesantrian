<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use DomainException;

final class SelfUserMutation extends DomainException
{
    public function __construct()
    {
        parent::__construct('Aktor tidak boleh mengarsipkan akunnya sendiri.');
    }
}
