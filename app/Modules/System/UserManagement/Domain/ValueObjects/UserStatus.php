<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\ValueObjects;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
}
