<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Domain\Exceptions;

use RuntimeException;

final class DuplicateUserEmail extends RuntimeException {}
