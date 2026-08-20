<?php

declare(strict_types=1);

$report = $argv[1] ?? '';

if ($report === '' || ! is_file($report)) {
    fwrite(STDERR, "Coverage Clover tidak ditemukan.\n");
    exit(2);
}

$document = new DOMDocument;
$document->preserveWhiteSpace = false;

if (! $document->load($report, LIBXML_NONET)) {
    fwrite(STDERR, "Coverage Clover tidak valid.\n");
    exit(2);
}

/** @var array<string, array{minimum: float, patterns: list<string>}> $layers */
$layers = [
    'Domain' => [
        'minimum' => 90.0,
        'patterns' => ['~/app/Modules/[^/]+/[^/]+/Domain/~'],
    ],
    'Application' => [
        'minimum' => 85.0,
        'patterns' => ['~/app/Modules/[^/]+/[^/]+/Application/~'],
    ],
    'Policy/security' => [
        'minimum' => 90.0,
        'patterns' => ['~/(Policies|Middleware)/~'],
    ],
    'Infrastructure' => [
        'minimum' => 75.0,
        'patterns' => ['~/app/Modules/[^/]+/[^/]+/Infrastructure/~'],
    ],
    'API/critical use case' => [
        'minimum' => 80.0,
        'patterns' => [
            '~/Presentation/Controllers/[^/]*ApiController\.php$~',
            '~/Presentation/Requests/[^/]*ApiRequest\.php$~',
            '~/Presentation/Resources/~',
        ],
    ],
    'Generator' => [
        'minimum' => 85.0,
        'patterns' => [
            '~/packages/StarterKit/src/Generator/~',
            '~/packages/StarterKit/src/Console/Commands/Module[^/]*Command\.php$~',
        ],
    ],
];

/** @var list<array{name: string, statements: int, covered: int}> $files */
$files = [];

foreach ($document->getElementsByTagName('file') as $file) {
    $metrics = $file->getElementsByTagName('metrics')->item(0);

    if (! $metrics instanceof DOMElement) {
        continue;
    }

    $files[] = [
        'name' => '/'.ltrim(str_replace('\\', '/', $file->getAttribute('name')), '/'),
        'statements' => (int) $metrics->getAttribute('statements'),
        'covered' => (int) $metrics->getAttribute('coveredstatements'),
    ];
}

$failed = false;

foreach ($layers as $name => $layer) {
    $statements = 0;
    $covered = 0;

    foreach ($files as $file) {
        if (! matchesAny($file['name'], $layer['patterns'])) {
            continue;
        }

        $statements += $file['statements'];
        $covered += $file['covered'];
    }

    if ($statements === 0) {
        fwrite(STDERR, sprintf("%s: tidak ada statement terukur.\n", $name));
        $failed = true;

        continue;
    }

    $coverage = round(($covered / $statements) * 100, 2);
    $passed = $coverage >= $layer['minimum'];
    printf(
        "%s: %.2f%% (%d/%d), minimum %.2f%% [%s]\n",
        $name,
        $coverage,
        $covered,
        $statements,
        $layer['minimum'],
        $passed ? 'LULUS' : 'GAGAL',
    );

    $failed = $failed || ! $passed;
}

exit($failed ? 1 : 0);

/** @param list<string> $patterns */
function matchesAny(string $path, array $patterns): bool
{
    foreach ($patterns as $pattern) {
        $result = preg_match($pattern, $path);

        if ($result === false) {
            throw new RuntimeException(sprintf('Pattern coverage tidak valid: %s', $pattern));
        }

        if ($result === 1) {
            return true;
        }
    }

    return false;
}
