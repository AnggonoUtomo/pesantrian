<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Asrama\Application\Actions\PlaceStudentInRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\RemoveStudentRoomPlacement;
use App\Modules\Pesantrian\Asrama\Application\Actions\TransferStudentRoom;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomTransferData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaPlacementException;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\PlaceStudentRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\RemoveStudentRoomPlacementApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\TransferStudentRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Resources\StudentRoomPlacementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentRoomPlacementApiController implements HasMiddleware
{
    public function __construct(
        private PlaceStudentInRoom $placeStudent,
        private TransferStudentRoom $transferStudent,
        private RemoveStudentRoomPlacement $removeStudent,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:asrama.placement', only: ['store', 'transfer', 'remove']),
        ];
    }

    public function store(PlaceStudentRoomApiRequest $request, string $dormitory): JsonResponse
    {
        try {
            $placement = $this->placeStudent->execute(
                $request->user(),
                $dormitory,
                (string) $request->validated('student_id'),
                (string) $request->validated('dormitory_room_id'),
                (string) $request->validated('started_at'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if($placement === null, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil ditempatkan ke kamar asrama.',
            (new StudentRoomPlacementResource($placement))->toArray($request),
            status: 201,
        );
    }

    public function transfer(TransferStudentRoomApiRequest $request, string $dormitory, string $placement): JsonResponse
    {
        try {
            $transfer = $this->transferStudent->execute(
                $request->user(),
                $dormitory,
                $placement,
                (string) $request->validated('target_room_id'),
                (string) $request->validated('started_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if(! $transfer instanceof StudentRoomTransferData, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil dipindahkan ke kamar asrama baru.',
            [
                'previous' => (new StudentRoomPlacementResource($transfer->previous))->toArray($request),
                'current' => (new StudentRoomPlacementResource($transfer->current))->toArray($request),
            ],
        );
    }

    public function remove(RemoveStudentRoomPlacementApiRequest $request, string $dormitory, string $placement): JsonResponse
    {
        try {
            $removed = $this->removeStudent->execute(
                $request->user(),
                $dormitory,
                $placement,
                (string) $request->validated('ended_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            return $this->invalidPlacement($request, $exception);
        }

        abort_if($removed === null, 404);

        return $this->responses->success(
            $request,
            'Santri berhasil dikeluarkan dari kamar asrama.',
            (new StudentRoomPlacementResource($removed))->toArray($request),
        );
    }

    private function invalidPlacement(
        PlaceStudentRoomApiRequest|RemoveStudentRoomPlacementApiRequest|TransferStudentRoomApiRequest $request,
        AsramaPlacementException $exception,
    ): JsonResponse {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'ASRAMA_PLACEMENT_INVALID',
            422,
            $exception->errors(),
        );
    }
}
