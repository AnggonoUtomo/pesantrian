<?php

use Illuminate\Support\Facades\Artisan;

it('menyediakan starter verify dalam format json tanpa secret', function () {
    $result = Artisan::call('starter:verify', ['--json' => true]);
    $output = Artisan::output();

    expect($result)->toBe(0)
        ->and($output)->toContain('STARTER_VERIFIED')
        ->and($output)->toContain('mysql_driver')
        ->and($output)->not->toContain((string) config('app.key'));
});

it('tidak membocorkan forbidden dependency pada output verification', function () {
    Artisan::call('starter:verify', ['--json' => true]);
    $output = strtolower(Artisan::output());

    expect($output)->not->toContain('wayfinder')
        ->and($output)->not->toContain('laravel boost');
});

it('menyediakan diagnosis dan health dalam format json tanpa secret', function () {
    $diagnose = Artisan::call('starter:diagnose', ['--json' => true]);
    $diagnoseOutput = Artisan::output();
    $health = Artisan::call('starter:health', ['--json' => true]);
    $healthOutput = Artisan::output();

    expect($diagnose)->toBe(0)
        ->and($diagnoseOutput)->toContain('STARTER_DIAGNOSED')
        ->and($diagnoseOutput)->toContain('modules')
        ->and($health)->toBe(0)
        ->and($healthOutput)->toContain('STARTER_HEALTHY')
        ->and($healthOutput)->not->toContain((string) config('app.key'));
});
