<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ApiResponseFactory
{
    /** @param array<string, mixed> $meta */
    public function success(
        Request $request,
        string $message,
        mixed $data,
        array $meta = [],
        int $status = 200,
    ): JsonResponse {
        return $this->response($request, [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $this->meta($request, $meta),
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $meta
     */
    public function error(
        Request $request,
        string $message,
        string $code,
        int $status,
        array $errors = [],
        array $meta = [],
    ): JsonResponse {
        return $this->response($request, [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
            'meta' => $this->meta($request, $meta),
        ], $status);
    }

    public function correlationId(Request $request): string
    {
        $existing = $request->attributes->get('api_correlation_id');

        if (is_string($existing) && Str::isUlid($existing)) {
            return $existing;
        }

        $provided = trim((string) $request->header('X-Correlation-ID'));
        $correlationId = Str::isUlid($provided) ? $provided : (string) Str::ulid();
        $request->attributes->set('api_correlation_id', $correlationId);

        return $correlationId;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function response(Request $request, array $payload, int $status): JsonResponse
    {
        return response()->json($payload, $status)
            ->header('X-Correlation-ID', $this->correlationId($request));
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function meta(Request $request, array $meta): array
    {
        return [
            'correlation_id' => $this->correlationId($request),
            ...$meta,
        ];
    }
}
