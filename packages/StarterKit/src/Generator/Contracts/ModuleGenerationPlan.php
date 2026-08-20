<?php

declare(strict_types=1);

namespace StarterKit\Generator\Contracts;

final readonly class ModuleGenerationPlan
{
    /**
     * @param  list<string>  $directories
     * @param  array<string, string>  $files
     */
    public function __construct(
        public string $profile,
        public string $targetPath,
        public array $directories,
        public array $files,
    ) {}
}
