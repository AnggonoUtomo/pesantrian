<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function coverageReportPath(): string
{
    return sys_get_temp_dir().DIRECTORY_SEPARATOR.'coverage-threshold-'.getmypid().'.xml';
}

function coverageVerifierPath(): string
{
    return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'tools'.DIRECTORY_SEPARATOR.'ci'.DIRECTORY_SEPARATOR.'verify-php-coverage.php';
}

/** @param array<string, array{statements: int, covered: int}> $files */
function writeCoverageReport(array $files): string
{
    $entries = '';

    foreach ($files as $path => $metrics) {
        $entries .= sprintf(
            '<file name="%s"><metrics statements="%d" coveredstatements="%d"/></file>',
            htmlspecialchars($path, ENT_XML1),
            $metrics['statements'],
            $metrics['covered'],
        );
    }

    $report = coverageReportPath();
    file_put_contents($report, '<coverage><project>'.$entries.'</project></coverage>');

    return $report;
}

function completeCoverageMetrics(int $domainCovered = 90): array
{
    return [
        '/workspace/app/Modules/System/Example/Domain/Rule.php' => ['statements' => 100, 'covered' => $domainCovered],
        '/workspace/app/Modules/System/Example/Application/Action.php' => ['statements' => 100, 'covered' => 85],
        '/workspace/app/Modules/System/Example/Presentation/Policies/ExamplePolicy.php' => ['statements' => 100, 'covered' => 90],
        '/workspace/app/Modules/System/Example/Infrastructure/Repository.php' => ['statements' => 100, 'covered' => 75],
        '/workspace/app/Modules/System/Example/Presentation/Controllers/ExampleApiController.php' => ['statements' => 100, 'covered' => 80],
        '/workspace/packages/StarterKit/src/Generator/Profile.php' => ['statements' => 100, 'covered' => 85],
    ];
}

afterEach(function (): void {
    @unlink(coverageReportPath());
});

it('menerima coverage yang memenuhi setiap threshold layer', function (): void {
    $process = new Process([
        PHP_BINARY,
        coverageVerifierPath(),
        writeCoverageReport(completeCoverageMetrics()),
    ]);

    $process->run();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getOutput())->toContain('Domain: 90.00%')
        ->and($process->getOutput())->toContain('Generator: 85.00%');
});

it('menolak layer di bawah threshold dan report yang tidak lengkap', function (): void {
    $belowThreshold = new Process([
        PHP_BINARY,
        coverageVerifierPath(),
        writeCoverageReport(completeCoverageMetrics(89)),
    ]);
    $belowThreshold->run();

    expect($belowThreshold->isSuccessful())->toBeFalse()
        ->and($belowThreshold->getOutput())->toContain('Domain: 89.00%')
        ->and($belowThreshold->getOutput())->toContain('[GAGAL]');

    $missingLayer = new Process([
        PHP_BINARY,
        coverageVerifierPath(),
        writeCoverageReport([
            '/workspace/app/Modules/System/Example/Domain/Rule.php' => ['statements' => 100, 'covered' => 100],
        ]),
    ]);
    $missingLayer->run();

    expect($missingLayer->isSuccessful())->toBeFalse()
        ->and($missingLayer->getErrorOutput())->toContain('Application: tidak ada statement terukur.');
});
