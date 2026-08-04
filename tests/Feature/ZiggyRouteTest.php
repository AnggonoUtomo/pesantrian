<?php

use Tighten\Ziggy\Ziggy;

it('membagikan route yang dibutuhkan frontend', function () {
    $routes = (new Ziggy)->toArray()['routes'];

    expect($routes)->toHaveKeys([
        'home',
        'login',
        'dashboard',
        'profile.edit',
        'two-factor.qr-code',
        'passkey.confirm',
    ]);
});

it('tidak membagikan route tanpa nama atau route internal yang tidak diizinkan', function () {
    $routes = (new Ziggy)->toArray()['routes'];

    expect($routes)->not->toHaveKeys([
        'storage.local',
        'storage.local.upload',
        'up',
    ]);
});