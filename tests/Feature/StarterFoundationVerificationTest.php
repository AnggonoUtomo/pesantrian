<?php

use Illuminate\Support\Facades\Artisan;

it('menyediakan starter verify dalam format json tanpa secret', function () {
    $result = Artisan::call('starter:verify', ['--json' => true]);
    $output = Artisan::output();

    expect($result)->toBe(0)
        ->and($output)->toContain('STARTER_VERIFIED')
        ->and($output)->not->toContain((string) config('app.key'));
});

it('tidak membocorkan forbidden dependency pada output verification', function () {
    Artisan::call('starter:verify', ['--json' => true]);
    $output = strtolower(Artisan::output());

    expect($output)->not->toContain('wayfinder')
        ->and($output)->not->toContain('laravel boost');
});
