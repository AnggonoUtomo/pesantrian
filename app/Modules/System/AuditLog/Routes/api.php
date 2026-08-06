<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Presentation\Controllers\AuditLogApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('api/v1/audit-logs')
    ->name('api.v1.audit-logs.')
    ->group(function (): void {
        Route::get('/', [AuditLogApiController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogApiController::class, 'show'])->name('show');
    });
