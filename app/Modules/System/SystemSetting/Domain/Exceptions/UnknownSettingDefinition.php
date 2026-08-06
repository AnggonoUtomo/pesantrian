<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Domain\Exceptions;

use InvalidArgumentException;

final class UnknownSettingDefinition extends InvalidArgumentException {}
