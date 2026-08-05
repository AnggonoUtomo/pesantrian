<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\DTO;

use InvalidArgumentException;

final readonly class ImpersonationRequestData
{
    public function __construct(
        public string $targetUserId,
        public string $reason,
    ) {
        if (trim($targetUserId) === '' || trim($reason) === '') {
            throw new InvalidArgumentException('Target user dan reason wajib diisi.');
        }

        if (mb_strlen($reason) > 500) {
            throw new InvalidArgumentException('Reason impersonation terlalu panjang.');
        }
    }
}
