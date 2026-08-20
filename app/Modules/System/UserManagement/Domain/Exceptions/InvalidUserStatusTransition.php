<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use InvalidArgumentException;

final class InvalidUserStatusTransition extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('Status user harus berbeda dari status saat ini.');
    }
}
