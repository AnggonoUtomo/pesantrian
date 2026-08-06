<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Domain\Exceptions;

use RuntimeException;

final class IdempotencyConflict extends RuntimeException {}
