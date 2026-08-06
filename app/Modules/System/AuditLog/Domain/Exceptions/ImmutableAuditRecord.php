<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Domain\Exceptions;

use LogicException;

final class ImmutableAuditRecord extends LogicException
{
    public function __construct()
    {
        parent::__construct('Audit record bersifat append-only dan tidak dapat diubah atau dihapus.');
    }
}
