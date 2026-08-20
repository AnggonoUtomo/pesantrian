<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Exceptions;

use RuntimeException;

final class InvitationDeliveryFailed extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Email undangan tidak dapat dikirim. Coba lagi setelah layanan email tersedia.');
    }
}
