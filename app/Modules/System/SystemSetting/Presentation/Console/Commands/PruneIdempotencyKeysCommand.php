<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Http\Idempotency\Contracts\IdempotencyRepository;

final class PruneIdempotencyKeysCommand extends Command
{
    protected $signature = 'system-setting:idempotency-prune {--json}';

    protected $description = 'Menghapus reservation idempotency yang sudah expired.';

    public function handle(IdempotencyRepository $repository): int
    {
        $deleted = $repository->pruneExpired();

        if ($this->option('json')) {
            $this->line((string) json_encode(['deleted' => $deleted], JSON_THROW_ON_ERROR));
        } else {
            $this->info("{$deleted} reservation idempotency expired dihapus.");
        }

        return self::SUCCESS;
    }
}
