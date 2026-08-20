<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Contracts;

use App\Modules\System\UserManagement\Application\DTO\InvitationMailSettings;
use App\Modules\System\UserManagement\Application\DTO\UserPaginationSettings;

interface UserRuntimeSettings
{
    public function pagination(): UserPaginationSettings;

    public function invitationMail(): InvitationMailSettings;
}
