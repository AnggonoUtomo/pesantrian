<?php

declare(strict_types=1);

use App\Support\StarterFoundation\ForbiddenDependencyScanner;
use Illuminate\Support\Str;

it('menemukan forbidden dependency pada file PHP nested', function () {
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'starter-scan-'.Str::ulid();
    $nested = $root.DIRECTORY_SEPARATOR.'deep'.DIRECTORY_SEPARATOR.'nested';

    mkdir($nested, 0755, true);
    file_put_contents($nested.DIRECTORY_SEPARATOR.'Forbidden.php', '<?php // Wayfinder');

    try {
        expect(app(ForbiddenDependencyScanner::class)->scan([$root]))
            ->toContain('Forbidden.php');
    } finally {
        unlink($nested.DIRECTORY_SEPARATOR.'Forbidden.php');
        rmdir($nested);
        rmdir(dirname($nested));
        rmdir($root);
    }
});
