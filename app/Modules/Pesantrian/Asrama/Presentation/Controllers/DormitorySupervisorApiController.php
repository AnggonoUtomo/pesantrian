<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Asrama\Application\Actions\AssignDormitorySupervisor;
use App\Modules\Pesantrian\Asrama\Application\Actions\EndDormitorySupervisor;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaSupervisorException;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\AssignDormitorySupervisorApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\EndDormitorySupervisorApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Resources\DormitorySupervisorAssignmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class DormitorySupervisorApiController implements HasMiddleware
{
    public function __construct(
        private AssignDormitorySupervisor $assignSupervisor,
        private EndDormitorySupervisor $endSupervisor,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:asrama.supervisor', only: ['store', 'end']),
        ];
    }

    public function store(AssignDormitorySupervisorApiRequest $request, string $dormitory): JsonResponse
    {
        try {
            $assignment = $this->assignSupervisor->execute(
                $request->user(),
                $dormitory,
                (string) $request->validated('employee_id'),
                $request->validated('dormitory_room_id') === null ? null : (string) $request->validated('dormitory_room_id'),
                (string) $request->validated('role'),
                (string) $request->validated('started_at'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaSupervisorException $exception) {
            return $this->invalidSupervisor($request, $exception);
        }

        abort_if($assignment === null, 404);

        return $this->responses->success(
            $request,
            'Musyrif asrama berhasil ditugaskan.',
            (new DormitorySupervisorAssignmentResource($assignment))->toArray($request),
            status: 201,
        );
    }

    public function end(EndDormitorySupervisorApiRequest $request, string $dormitory, string $assignment): JsonResponse
    {
        try {
            $ended = $this->endSupervisor->execute(
                $request->user(),
                $dormitory,
                $assignment,
                (string) $request->validated('ended_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaSupervisorException $exception) {
            return $this->invalidSupervisor($request, $exception);
        }

        abort_if($ended === null, 404);

        return $this->responses->success(
            $request,
            'Tugas musyrif asrama berhasil diakhiri.',
            (new DormitorySupervisorAssignmentResource($ended))->toArray($request),
        );
    }

    private function invalidSupervisor(
        AssignDormitorySupervisorApiRequest|EndDormitorySupervisorApiRequest $request,
        AsramaSupervisorException $exception,
    ): JsonResponse {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'ASRAMA_SUPERVISOR_INVALID',
            422,
            $exception->errors(),
        );
    }
}
