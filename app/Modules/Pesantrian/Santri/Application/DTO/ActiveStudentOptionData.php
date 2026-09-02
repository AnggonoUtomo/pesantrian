<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\DTO;

final readonly class ActiveStudentOptionData
{
    public function __construct(
        public string $id,
        public string $studentNo,
        public string $fullName,
        public ?string $primaryUnitId,
    ) {}
}
