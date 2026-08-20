<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

try {
    $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();

    if (! $app->environment('testing')) {
        throw new RuntimeException('Browser runtime hanya boleh disiapkan pada environment testing.');
    }

    if (DB::connection()->getDriverName() !== 'sqlite') {
        throw new RuntimeException('Browser runtime wajib memakai database SQLite disposable.');
    }

    $database = (string) config('database.connections.sqlite.database');
    $runtimeRoot = str_replace('\\', '/', base_path('build/browser-runtime/'));
    $normalizedDatabase = str_replace('\\', '/', $database);

    if (! str_starts_with($normalizedDatabase, $runtimeRoot)) {
        throw new RuntimeException('Database browser runtime harus berada di build/browser-runtime.');
    }

    if (config('mail.mailers.log.channel') !== 'null') {
        throw new RuntimeException('Browser runtime wajib mengarahkan mailer log ke channel null.');
    }

    $updated = DB::table('system_settings')
        ->where('key', 'mail.mailer')
        ->update([
            'value' => json_encode('log', JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

    if ($updated !== 1) {
        throw new RuntimeException('Fixture mail browser runtime tidak dapat disiapkan.');
    }

    fwrite(STDOUT, "Browser runtime siap dengan mail transport log ke channel null.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Browser runtime gagal: {$exception->getMessage()}\n");

    exit(1);
}
