<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Middleware;

use App\Modules\System\SystemSetting\Application\Services\IdempotencyManager;
use App\Modules\System\SystemSetting\Domain\Exceptions\IdempotencyConflict;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class EnforceSystemSettingIdempotency
{
    public function __construct(
        private IdempotencyManager $idempotency,
        private LoggerInterface $logger,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $key = trim((string) $request->header('Idempotency-Key'));

        if ($key === '' || mb_strlen($key) > 120 || preg_match('/^[A-Za-z0-9._:-]+$/', $key) !== 1) {
            return response()->json(['message' => 'Idempotency-Key wajib diisi.'], 422);
        }

        $actorId = (string) $request->user()?->getAuthIdentifier();

        if (! Str::isUlid($actorId)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $endpoint = $request->method().' '.$request->path();

        try {
            $decision = $this->idempotency->begin($actorId, $endpoint, $key, $request->all());
        } catch (IdempotencyConflict $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        if ($decision->isReplay()) {
            return response()->json($decision->responseBody, $decision->responseStatus)
                ->header('Idempotency-Replayed', 'true');
        }

        try {
            return DB::transaction(function () use ($decision, $next, $request): Response {
                $response = $next($request);

                if ($response instanceof JsonResponse && $response->isSuccessful()) {
                    /** @var array<string, mixed> $body */
                    $body = $response->getData(true);
                    $this->idempotency->complete($decision->reservationId, $response->getStatusCode(), $body);
                } else {
                    $this->idempotency->cancel($decision->reservationId);
                }

                return $response;
            });
        } catch (Throwable $exception) {
            try {
                $this->idempotency->cancel($decision->reservationId);
            } catch (Throwable $cancelException) {
                $this->logger->warning('Reservation idempotency gagal dibatalkan.', [
                    'failure_type' => $cancelException::class,
                ]);
            }

            throw $exception;
        }
    }
}
