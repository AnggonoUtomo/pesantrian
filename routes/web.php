<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome')->name('home');

Route::get('system/login', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('system.dashboard');
    }

    $request->session()->put('url.intended', route('system.dashboard'));

    return Inertia::render('auth/login', [
        'area' => 'system',
        'canResetPassword' => Features::enabled(Features::resetPasswords()),
        'status' => $request->session()->get('status'),
    ]);
})->name('system.login');

require __DIR__.'/settings.php';
