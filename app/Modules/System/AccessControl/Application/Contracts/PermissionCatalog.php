<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Application\Contracts;

interface PermissionCatalog
{
    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    public function existingNames(array $names, string $guardName): array;
}
