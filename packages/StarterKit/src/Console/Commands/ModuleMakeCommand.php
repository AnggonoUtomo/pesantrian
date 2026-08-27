<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationRequest;
use StarterKit\Generator\ModuleGenerationPreviewer;
use StarterKit\Generator\ModulePromotionService;
use Throwable;

final class ModuleMakeCommand extends Command
{
    protected $signature = 'module:make
        {namespace : Namespace module dalam PascalCase, atau nama module saat memakai --domain legacy}
        {module? : Nama module dalam PascalCase}
        {--domain=System : Alias kompatibilitas untuk namespace module}
        {--profile=default-v1 : Profile generator}
        {--dry-run : Tampilkan rencana tanpa menulis file}
        {--force : Wajib untuk mengizinkan operasi mutasi}
        {--yes : Lewati konfirmasi interaktif setelah force}
        {--extension : Izinkan bekerja pada module existing}
        {--overwrite : Ganti file existing yang tercantum pada plan}
        {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Membuat module baru dari profile generator.';

    public function handle(ModuleGenerationPreviewer $previewer, ModulePromotionService $promotion): int
    {
        try {
            $moduleArgument = $this->argument('module');
            $namespace = is_string($moduleArgument) && $moduleArgument !== ''
                ? $this->argument('namespace')
                : $this->option('domain');
            $module = is_string($moduleArgument) && $moduleArgument !== ''
                ? $moduleArgument
                : $this->argument('namespace');

            $request = ModuleGenerationRequest::fromArray([
                'module' => $module,
                'namespace' => $namespace,
                'profile' => $this->option('profile'),
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'yes' => (bool) $this->option('yes'),
                'extension' => (bool) $this->option('extension'),
                'overwrite' => (bool) $this->option('overwrite'),
            ]);

            if (! $request->dryRun && ! $request->force) {
                throw new InvalidArgumentException('Pembuatan module membutuhkan --force; gunakan --dry-run untuk preview.');
            }

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
                $request->extension,
                $request->overwrite,
            );

            return $this->respond([
                'success' => true,
                'code' => 'MODULE_CREATED',
                'message' => 'Module berhasil dibuat.',
                'data' => ['target' => $result->targetPath, 'files' => array_keys($preview->plan->files)],
                'diagnostics' => [],
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->respond([
                'success' => false,
                'code' => 'MODULE_GENERATION_INVALID',
                'message' => $exception->getMessage(),
                'data' => [],
                'diagnostics' => [],
            ]);
        } catch (Throwable) {
            return $this->respond([
                'success' => false,
                'code' => 'MODULE_GENERATION_FAILED',
                'message' => 'Generator module gagal dan perubahan telah dibersihkan.',
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
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
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
