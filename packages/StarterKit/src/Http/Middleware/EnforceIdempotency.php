<?php

declare(strict_types=1);

namespace StarterKit\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use StarterKit\Http\Idempotency\IdempotencyManager;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class EnforceIdempotency
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
            throw ValidationException::withMessages([
                'idempotency_key' => ['Idempotency-Key wajib diisi dan memakai format yang valid.'],
            ]);
        }

        $actorId = (string) $request->user()?->getAuthIdentifier();

        if (! Str::isUlid($actorId)) {
            throw new AuthenticationException('Authentication diperlukan.');
        }

        $endpoint = $request->method().' '.$request->path();
        $decision = $this->idempotency->begin($actorId, $endpoint, $key, $request->all());

        if ($decision->isReplay()) {
            $response = response()->json($decision->responseBody, $decision->responseStatus)
                ->header('Idempotency-Replayed', 'true');
            $correlationId = data_get($decision->responseBody, 'meta.correlation_id');

            return is_string($correlationId) && Str::isUlid($correlationId)
                ? $response->header('X-Correlation-ID', $correlationId)
                : $response;
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
