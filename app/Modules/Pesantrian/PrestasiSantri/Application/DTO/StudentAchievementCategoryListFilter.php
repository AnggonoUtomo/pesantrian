<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementCategoryListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $status,
    ) {}
}
