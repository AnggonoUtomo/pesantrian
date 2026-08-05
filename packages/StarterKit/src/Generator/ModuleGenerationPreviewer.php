<?php

declare(strict_types=1);

namespace StarterKit\Generator;

use StarterKit\Generator\Contracts\ModuleGenerationPreview;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\Profiles\DefaultModuleProfile;

final class ModuleGenerationPreviewer
{
    public function __construct(
        private DefaultModuleProfile $profile,
        private ModuleConflictDetector $conflicts,
    ) {}

    public function preview(ModuleGenerationRequest $request, string $rootPath): ModuleGenerationPreview
    {
        $plan = $this->profile->plan($request);

        return new ModuleGenerationPreview(
            plan: $plan,
            diagnostics: $this->conflicts->detect($plan, $rootPath, $request->extension),
        );
    }
}
