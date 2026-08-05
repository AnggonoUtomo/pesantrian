<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\StarterFoundation\FoundationCommandResponse;
use App\Support\StarterFoundation\StarterFoundationCheckService;
use Illuminate\Console\Command;

final class DiagnoseStarterFoundation extends Command
{
    protected $signature = 'starter:diagnose {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Menampilkan diagnosis environment dan module tanpa secret.';

    public function handle(StarterFoundationCheckService $checks, FoundationCommandResponse $response): int
    {
        return $response->respond($this, $checks->diagnose());
    }
}
