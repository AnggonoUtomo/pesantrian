<?php

declare(strict_types=1);

namespace StarterKit\Generator\Contracts;

final readonly class ModuleGenerationPromotion
{
    public function __construct(public string $targetPath) {}
}
