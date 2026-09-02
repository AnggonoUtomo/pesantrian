<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class EducationUnitOptionData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
    ) {}
}
