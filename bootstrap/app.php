<?php

use App\Http\ApiResponseFactory;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\System\AccessControl\Domain\Exceptions\DuplicateRole;
use App\Modules\System\AccessControl\Domain\Exceptions\ProtectedRoleMutation;
use App\Modules\System\AuditLog\Domain\Exceptions\SensitiveAuditReason;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use App\Modules\System\SystemSetting\Presentation\Middleware\EnforceConfiguredSessionLifetime;
use App\Modules\System\UserManagement\Domain\Exceptions\DuplicateUserEmail;
use App\Modules\System\UserManagement\Domain\Exceptions\InvalidUserStatusTransition;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\Exceptions\SelfUserMutation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use StarterKit\Http\Idempotency\Exceptions\IdempotencyConflict;
use StarterKit\Http\Idempotency\Exceptions\IdempotencyStorageUnavailable;
use StarterKit\Http\Middleware\EnforceIdempotency;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'api.idempotency' => EnforceIdempotency::class,
        ]);

        $middleware->web(append: [
            EnsureActiveUser::class,
            EnforceConfiguredSessionLifetime::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (SettingStorageUnavailable $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Layanan konfigurasi sementara tidak tersedia.',
                    'SERVICE_UNAVAILABLE',
                    503,
                );
            }

            return null;
        });

        $exceptions->render(function (IdempotencyStorageUnavailable $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Layanan idempotency sementara tidak tersedia.',
                    'SERVICE_UNAVAILABLE',
                    503,
                );
            }

            return null;
        });

        $exceptions->render(function (IdempotencyConflict $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    $exception->getMessage(),
                    'IDEMPOTENCY_CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (DuplicateUserEmail $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Email user sudah digunakan.',
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (DuplicateRole $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Role sudah tersedia.',
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (ProtectedRoleMutation $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Role yang dilindungi tidak dapat diubah.',
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (InvalidUserStatusTransition $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    $exception->getMessage(),
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (ProtectedUserMutation $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'User yang dilindungi tidak dapat diubah.',
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (SelfUserMutation $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Aktor tidak boleh mengarsipkan akunnya sendiri.',
                    'CONFLICT',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (SensitiveAuditReason $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Request tidak valid.',
                    'VALIDATION_ERROR',
                    422,
                    ['reason' => [$exception->getMessage()]],
                );
            }

            return back()->withErrors(['reason' => $exception->getMessage()]);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Akses tidak diizinkan.',
                    'FORBIDDEN',
                    403,
                );
            }

            return Inertia::render('errors/unauthorized', [
                'status' => 403,
                'message' => 'Akunmu belum memiliki permission untuk area ini.',
            ])->toResponse($request)->setStatusCode(403);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Authentication diperlukan.',
                    'UNAUTHENTICATED',
                    401,
                );
            }

            return null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Request tidak valid.',
                    'VALIDATION_ERROR',
                    422,
                    $exception->errors(),
                );
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(ApiResponseFactory::class)->error(
                    $request,
                    'Resource tidak ditemukan.',
                    'RESOURCE_NOT_FOUND',
                    404,
                );
            }

            return null;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = match ($status) {
                403 => 'Akses tidak diizinkan.',
                429 => 'Batas request terlampaui.',
                default => 'Request tidak dapat diproses.',
            };
            $code = match ($status) {
                403 => 'FORBIDDEN',
                429 => 'RATE_LIMITED',
                default => 'HTTP_ERROR',
            };

            return app(ApiResponseFactory::class)->error(
                $request,
                $message,
                $code,
                $status,
                meta: $status === 429
                    ? ['retry_after' => $exception->getHeaders()['Retry-After'] ?? null]
                    : [],
            );
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
