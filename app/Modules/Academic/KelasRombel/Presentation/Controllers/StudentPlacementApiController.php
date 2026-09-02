<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\PlaceStudent;
use App\Modules\Academic\KelasRombel\Application\Actions\RemoveStudentPlacement;
use App\Modules\Academic\KelasRombel\Application\Actions\TransferStudent;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentTransferData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelPlacementException;
use App\Modules\Academic\KelasRombel\Presentation\Requests\PlaceStudentApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\RemoveStudentPlacementApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\TransferStudentApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\StudentPlacementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentPlacementApiController implements HasMiddleware
{
    public function __construct(
        private PlaceStudent $placeStudent,
        private TransferStudent $transferStudent,
        private RemoveStudentPlacement $removeStudent,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.placement', only: ['store', 'transfer', 'remove']),
        ];
    }

    public function store(PlaceStudentApiRequest $request, string $classGroup): JsonResponse
    {
        try {
            $placement = $this->placeStudent->execute(
                $request->user(),
                $classGroup,
                (string) $request->validated('student_id'),
                (string) $request->validated('joined_on'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if($placement === null, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil ditempatkan ke rombel.',
            (new StudentPlacementResource($placement))->toArray($request),
            status: 201,
        );
    }

    public function transfer(TransferStudentApiRequest $request, string $classGroup, string $placement): JsonResponse
    {
        try {
            $transfer = $this->transferStudent->execute(
                $request->user(),
                $classGroup,
                $placement,
                (string) $request->validated('target_class_group_id'),
                (string) $request->validated('joined_on'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if(! $transfer instanceof StudentTransferData, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil dipindahkan ke rombel baru.',
            [
                'previous' => (new StudentPlacementResource($transfer->previous))->toArray($request),
                'current' => (new StudentPlacementResource($transfer->current))->toArray($request),
            ],
        );
    }

    public function remove(RemoveStudentPlacementApiRequest $request, string $classGroup, string $placement): JsonResponse
    {
        try {
            $removed = $this->removeStudent->execute(
                $request->user(),
                $classGroup,
                $placement,
                (string) $request->validated('left_on'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if($removed === null, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil dikeluarkan dari rombel.',
            (new StudentPlacementResource($removed))->toArray($request),
        );
    }

    private function invalidPlacement(PlaceStudentApiRequest|RemoveStudentPlacementApiRequest|TransferStudentApiRequest $request, KelasRombelPlacementException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'KELAS_ROMBEL_PLACEMENT_INVALID',
            422,
            $exception->errors(),
        );
    }
}
