<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateCurriculum;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateCurriculum;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreCurriculumApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateCurriculumApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\CurriculumResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class CurriculumApiController implements HasMiddleware
{
    public function __construct(
        private CreateCurriculum $createCurriculum,
        private UpdateCurriculum $updateCurriculum,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.manage', only: ['store', 'update']),
        ];
    }

    public function store(StoreCurriculumApiRequest $request): JsonResponse
    {
        $curriculum = $this->createCurriculum->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Kurikulum berhasil dibuat.',
            (new CurriculumResource($curriculum))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateCurriculumApiRequest $request, string $curriculum): JsonResponse
    {
        $updated = $this->updateCurriculum->execute(
            $request->user(),
            $curriculum,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Kurikulum berhasil diperbarui.',
            (new CurriculumResource($updated))->toArray($request),
        );
    }
}
