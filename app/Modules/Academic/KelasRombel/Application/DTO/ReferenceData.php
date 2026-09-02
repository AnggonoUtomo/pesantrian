<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ReferenceData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
    ) {}
}
