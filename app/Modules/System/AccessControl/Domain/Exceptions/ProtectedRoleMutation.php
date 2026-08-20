<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Domain\Exceptions;

use DomainException;

final class ProtectedRoleMutation extends DomainException
{
    public function __construct()
    {
        parent::__construct('Role yang dilindungi tidak dapat diubah.');
    }
}
