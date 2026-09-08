<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCategoryListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $status,
    ) {}
}
