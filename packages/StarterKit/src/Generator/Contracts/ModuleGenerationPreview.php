<?php

declare(strict_types=1);

namespace StarterKit\Generator\Contracts;

final readonly class ModuleGenerationPreview
{
    /** @param list<array{code: string, message: string}> $diagnostics */
    public function __construct(
        public ModuleGenerationPlan $plan,
        public array $diagnostics,
    ) {}

    public function isValid(): bool
    {
        return $this->diagnostics === [];
    }
}
