<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\Queries\ListDormitories;
use App\Modules\Pesantrian\Asrama\Application\Queries\ShowDormitory;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\ListDormitoriesApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Resources\DormitoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class DormitoryApiController implements HasMiddleware
{
    public function __construct(
        private ListDormitories $listDormitories,
        private ShowDormitory $showDormitory,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:asrama.view', only: ['index', 'show']),
        ];
    }

    public function index(ListDormitoriesApiRequest $request): JsonResponse
    {
        $result = $this->listDormitories->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar asrama berhasil dibaca.',
            array_map(
                static fn (DormitoryData $dormitory): array => (new DormitoryResource($dormitory))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $dormitory): JsonResponse
    {
        $data = $this->showDormitory->execute($dormitory);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail asrama berhasil dibaca.',
            (new DormitoryResource($data, includeDetails: true))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedDormitoryData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
