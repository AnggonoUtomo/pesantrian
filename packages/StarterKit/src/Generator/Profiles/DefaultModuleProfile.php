<?php

declare(strict_types=1);

namespace StarterKit\Generator\Profiles;

use InvalidArgumentException;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;

final class DefaultModuleProfile
{
    public function plan(ModuleGenerationRequest $request): ModuleGenerationPlan
    {
        if ($request->profile !== 'default-v1') {
            throw new InvalidArgumentException('DefaultModuleProfile hanya mendukung profile default-v1.');
        }

        $namespace = 'App\\Modules\\'.$request->namespace.'\\'.$request->module;
        $targetPath = 'app/Modules/'.$request->namespace.'/'.$request->module;

        return new ModuleGenerationPlan(
            profile: $request->profile,
            targetPath: $targetPath,
            directories: $this->directories(),
            files: $this->files($request, $namespace),
        );
    }

    /** @return list<string> */
    private function directories(): array
    {
        return [];
    }

    /** @return array<string, string> */
    private function files(ModuleGenerationRequest $request, string $namespace): array
    {
        $manifest = [
            'name' => $request->module,
            'namespace' => $namespace,
            'version' => '1.0.0',
            'schema_version' => 1,
            'status' => 'enabled',
            'domain' => $request->namespace,
            'path' => 'app/Modules/'.$request->namespace.'/'.$request->module,
            'provider' => $namespace.'\\ServiceProvider',
            'dependencies' => [],
            'permission_source' => 'permissions.php',
            'config_source' => 'module.php',
        ];

        return [
            'module.json' => json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
            'module.php' => "<?php\n\nreturn [];\n",
            'permissions.php' => "<?php\n\nreturn [];\n",
            'ServiceProvider.php' => "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\nuse Illuminate\\Support\\ServiceProvider as FrameworkServiceProvider;\n\nfinal class ServiceProvider extends FrameworkServiceProvider\n{\n}\n",
            'README.md' => "# {$request->module}\n\nModule {$request->module} pada namespace {$request->namespace}.\n",
            'Routes/api.php' => "<?php\n",
            'Routes/web.php' => "<?php\n",
            'Routes/console.php' => "<?php\n",
            'Routes/channels.php' => "<?php\n",
        ];
    }
}
