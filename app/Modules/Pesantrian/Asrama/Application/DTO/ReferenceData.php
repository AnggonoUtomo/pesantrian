<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class ReferenceData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
    ) {}
}
