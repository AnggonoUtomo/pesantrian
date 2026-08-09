<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Domain\Exceptions;

use DomainException;

final class SensitiveAuditReason extends DomainException
{
    public function __construct()
    {
        parent::__construct('Reason tidak boleh memuat password, token, atau credential.');
    }
}
