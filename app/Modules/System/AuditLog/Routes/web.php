<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Presentation\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('system/audit-logs')
    ->name('system.audit-logs.')
    ->group(function (): void {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });
