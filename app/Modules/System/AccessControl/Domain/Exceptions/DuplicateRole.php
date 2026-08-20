<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Domain\Exceptions;

use DomainException;
use Throwable;

final class DuplicateRole extends DomainException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Role sudah tersedia.', previous: $previous);
    }
}
