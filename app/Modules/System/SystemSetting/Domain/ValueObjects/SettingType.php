<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Domain\ValueObjects;

enum SettingType: string
{
    case Integer = 'integer';
    case Boolean = 'boolean';
    case String = 'string';
    case Enum = 'enum';
    case Path = 'path';
    case IntegerList = 'integer_list';
    case Secret = 'secret';
}
