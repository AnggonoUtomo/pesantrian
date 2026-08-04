<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\ModuleGenerationPreviewer;
use StarterKit\Generator\ModulePromotionService;
use Throwable;

final class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make
        {module : Nama module dalam PascalCase}
        {--domain=System : Domain module dalam PascalCase}
        {--profile=default-v1 : Profile generator}
        {--dry-run : Tampilkan rencana tanpa menulis file}
        {--force : Konfirmasi mode mutasi untuk caller non-interactive}
        {--yes : Lewati konfirmasi interaktif setelah force}
        {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Membuat module baru dari profile generator.';

    public function handle(ModuleGenerationPreviewer $previewer, ModulePromotionService $promotion): int
    {
        try {
            $request = ModuleGenerationRequest::fromArray([
                'module' => $this->argument('module'),
                'domain' => $this->option('domain'),
                'profile' => $this->option('profile'),
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'yes' => (bool) $this->option('yes'),
            ]);
            $preview = $previewer->preview($request, app_path('Modules'));

            if (! $preview->isValid()) {
                return $this->respond([
                    'success' => false,
                    'code' => 'MODULE_GENERATION_FAILED',
                    'message' => 'Module tidak dapat dibuat karena conflict atau diagnostic.',
                    'data' => ['target' => $preview->plan->targetPath],
                    'diagnostics' => $preview->diagnostics,
                ]);
            }

            if ($request->dryRun) {
                return $this->respond([
                    'success' => true,
                    'code' => 'MODULE_PREVIEWED',
                    'message' => 'Rencana module valid; tidak ada file yang ditulis.',
                    'data' => $this->planData($preview->plan),
                    'diagnostics' => [],
                ]);
            }

            $result = $promotion->promote(
                $preview->plan,
                app_path('Modules'),
                storage_path('framework/module-staging'),
            );

            return $this->respond([
                'success' => true,
                'code' => 'MODULE_CREATED',
                'message' => 'Module berhasil dibuat.',
                'data' => ['target' => $result->targetPath, 'files' => array_keys($preview->plan->files)],
                'diagnostics' => [],
            ]);
        } catch (Throwable $exception) {
            return $this->respond([
                'success' => false,
                'code' => 'MODULE_GENERATION_INVALID',
                'message' => $exception->getMessage(),
                'data' => [],
                'diagnostics' => [],
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function planData(ModuleGenerationPlan $plan): array
    {
        return [
            'profile' => $plan->profile,
            'target' => $plan->targetPath,
            'directories' => $plan->directories,
            'files' => array_keys($plan->files),
        ];
    }

    /** @param array<string, mixed> $payload */
    private function respond(array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($payload['success']) {
            $this->info((string) $payload['message']);
            if (isset($payload['data']['target'])) {
                $this->line('Target: '.$payload['data']['target']);
            }
        } else {
            $this->error((string) $payload['message']);
            foreach ($payload['diagnostics'] as $diagnostic) {
                $this->error($diagnostic['code'].': '.$diagnostic['message']);
            }
        }

        return $payload['success'] ? self::SUCCESS : self::FAILURE;
    }
}
