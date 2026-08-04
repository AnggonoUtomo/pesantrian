<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Permission as SpatiePermission;

final class Permission extends SpatiePermission
{
    use HasUlids;

    protected $keyType = 'string';

    public $incrementing = false;
}
