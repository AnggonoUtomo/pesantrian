<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateClassLevel;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateClassLevel;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreClassLevelApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateClassLevelApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\ClassLevelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class ClassLevelApiController implements HasMiddleware
{
    public function __construct(
        private CreateClassLevel $createClassLevel,
        private UpdateClassLevel $updateClassLevel,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.manage', only: ['store', 'update']),
        ];
    }

    public function store(StoreClassLevelApiRequest $request): JsonResponse
    {
        $level = $this->createClassLevel->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Tingkat kelas berhasil dibuat.',
            (new ClassLevelResource($level))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateClassLevelApiRequest $request, string $level): JsonResponse
    {
        $updated = $this->updateClassLevel->execute(
            $request->user(),
            $level,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Tingkat kelas berhasil diperbarui.',
            (new ClassLevelResource($updated))->toArray($request),
        );
    }
}
