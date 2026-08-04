<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Console\Commands;

use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use Illuminate\Console\Command;

final class SeedAccessControlCommand extends Command
{
    protected $signature = 'access-control:seed';

    protected $description = 'Membuat permission, role, dan user demo AccessControl.';

    public function handle(AccessControlSeeder $seeder): int
    {
        $seeder->run();

        $this->info('Seeder AccessControl selesai.');

        return self::SUCCESS;
    }
}
