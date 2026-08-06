<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Application\Actions\RecordAuditEntry;
use App\Modules\System\AuditLog\Application\Queries\GetAuditLog;
use App\Modules\System\AuditLog\Application\Queries\ListAuditLogs;
use App\Modules\System\AuditLog\Presentation\Controllers\AuditLogApiController;
use App\Modules\System\AuditLog\Presentation\Controllers\AuditLogController;

it('menjaga controller AuditLog sebagai orchestration layer yang tipis', function (): void {
    foreach ([AuditLogController::class, AuditLogApiController::class] as $controllerClass) {
        $source = file_get_contents((new ReflectionClass($controllerClass))->getFileName());

        expect($source)
            ->not->toContain('AuditRecord::query(')
            ->not->toContain('DB::')
            ->not->toContain('$request->validate(')
            ->toContain('ListAuditLogs')
            ->toContain('GetAuditLog')
            ->toContain("'can:viewAny,'.AuditRecord::class");
    }
});

it('memisahkan mutation dan query AuditLog pada application boundary', function (): void {
    expect(class_exists(RecordAuditEntry::class))->toBeTrue()
        ->and(class_exists(ListAuditLogs::class))->toBeTrue()
        ->and(class_exists(GetAuditLog::class))->toBeTrue();
});

it('tidak membuat producer bergantung pada implementation AuditLog', function (): void {
    foreach ([
        app_path('Modules/System/AccessControl'),
        app_path('Modules/System/UserManagement'),
    ] as $path) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            expect(file_get_contents($file->getPathname()))
                ->not->toContain('App\\Modules\\System\\AuditLog\\');
        }
    }
});
