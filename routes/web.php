<?php

use App\Modules\System\AccessControl\Presentation\Controllers\RoleController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [RoleController::class, 'dashboard'])
        ->name('dashboard');
    Route::get('system/dashboard', [RoleController::class, 'dashboard'])
        ->name('system.dashboard');
    Route::get('system/access-control', [RoleController::class, 'index'])
        ->name('access-control.index');
    Route::post('system/access-control/roles', [RoleController::class, 'store'])
        ->name('access-control.roles.store');
    Route::put('system/access-control/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
        ->name('access-control.roles.permissions.update');
    Route::delete('system/access-control/roles/{role}', [RoleController::class, 'destroy'])
        ->name('access-control.roles.destroy');
});

require __DIR__.'/settings.php';
