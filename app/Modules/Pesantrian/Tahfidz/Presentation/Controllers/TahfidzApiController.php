<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ListTahfidzSubmissions;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ShowTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\ListTahfidzSubmissionsApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzSubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class TahfidzApiController implements HasMiddleware
{
    public function __construct(
        private ListTahfidzSubmissions $listTahfidzSubmissions,
        private ShowTahfidzSubmission $showTahfidzSubmission,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:tahfidz.view', only: ['index', 'show']),
        ];
    }

    public function index(ListTahfidzSubmissionsApiRequest $request): JsonResponse
    {
        $result = $this->listTahfidzSubmissions->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar setoran tahfidz berhasil dibaca.',
            array_map(
                static fn (TahfidzSubmissionData $submission): array => (new TahfidzSubmissionResource($submission, includeRevisions: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $submission): JsonResponse
    {
        $data = $this->showTahfidzSubmission->execute($submission);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail setoran tahfidz berhasil dibaca.',
            (new TahfidzSubmissionResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedTahfidzSubmissionData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
