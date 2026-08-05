<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\StarterFoundation\FoundationCommandResponse;
use App\Support\StarterFoundation\StarterFoundationCheckService;
use Illuminate\Console\Command;

final class HealthStarterFoundation extends Command
{
    protected $signature = 'starter:health {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Memeriksa health dependency runtime tanpa secret.';

    public function handle(StarterFoundationCheckService $checks, FoundationCommandResponse $response): int
    {
        return $response->respond($this, $checks->health());
    }
}
