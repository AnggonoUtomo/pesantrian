<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Infrastructure\Runtime\DefaultAuditRuntimeSettings;
use App\Modules\System\UserManagement\Infrastructure\Runtime\DefaultUserRuntimeSettings;
use Illuminate\Config\Repository;

it('menyediakan default runtime UserManagement tanpa SystemSetting', function (): void {
    $config = new Repository;
    $config->set([
        'user-management.pagination.per_page_options' => [10, 25, 50],
        'user-management.pagination.default_per_page' => 25,
        'user-management.invitation.reset_expire_minutes' => 90,
        'mail.default' => 'log',
        'mail.mailers.smtp.host' => '127.0.0.1',
        'mail.mailers.smtp.port' => 2525,
        'mail.mailers.smtp.username' => null,
        'mail.mailers.smtp.password' => null,
        'mail.from.address' => 'noreply@example.test',
        'mail.from.name' => 'Starter',
    ]);

    $runtime = new DefaultUserRuntimeSettings($config);

    expect($runtime->pagination()->perPageOptions)->toBe([10, 25, 50])
        ->and($runtime->pagination()->defaultPerPage)->toBe(25)
        ->and($runtime->invitationMail()->mailer)->toBe('log')
        ->and($runtime->invitationMail()->port)->toBe(2525)
        ->and($runtime->invitationMail()->resetExpireMinutes)->toBe(90);
});

it('menyediakan default runtime AuditLog tanpa SystemSetting', function (): void {
    $config = new Repository;
    $config->set([
        'audit-log.pagination.per_page_options' => [10, 20],
        'audit-log.pagination.default_per_page' => 10,
    ]);

    $runtime = new DefaultAuditRuntimeSettings($config);

    expect($runtime->pagination()->perPageOptions)->toBe([10, 20])
        ->and($runtime->pagination()->defaultPerPage)->toBe(10);
});

it('melarang consumer mengimpor namespace SystemSetting', function (): void {
    $roots = [
        dirname(__DIR__, 2).'/app/Modules/System/UserManagement',
        dirname(__DIR__, 2).'/app/Modules/System/AuditLog',
    ];

    foreach ($roots as $root) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            expect(file_get_contents($file->getPathname()))
                ->not->toContain('App\\Modules\\System\\SystemSetting\\');
        }
    }
});
