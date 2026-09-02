<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\AssignHomeroom;
use App\Modules\Academic\KelasRombel\Application\Actions\EndHomeroom;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelHomeroomException;
use App\Modules\Academic\KelasRombel\Presentation\Requests\AssignHomeroomApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\EndHomeroomApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\HomeroomAssignmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class HomeroomAssignmentApiController implements HasMiddleware
{
    public function __construct(
        private AssignHomeroom $assignHomeroom,
        private EndHomeroom $endHomeroom,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.manage', only: ['store', 'end']),
        ];
    }

    public function store(AssignHomeroomApiRequest $request, string $classGroup): JsonResponse
    {
        try {
            $homeroom = $this->assignHomeroom->execute(
                $request->user(),
                $classGroup,
                (string) $request->validated('employee_id'),
                (string) $request->validated('assigned_on'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelHomeroomException $exception) {
            return $this->invalidHomeroom($request, $exception);
        }

        abort_if($homeroom === null, 404);

        return $this->responses->success(
            $request,
            'Wali kelas berhasil ditetapkan.',
            (new HomeroomAssignmentResource($homeroom))->toArray($request),
            status: 201,
        );
    }

    public function end(EndHomeroomApiRequest $request, string $classGroup, string $homeroom): JsonResponse
    {
        try {
            $ended = $this->endHomeroom->execute(
                $request->user(),
                $classGroup,
                $homeroom,
                (string) $request->validated('ended_on'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelHomeroomException $exception) {
            return $this->invalidHomeroom($request, $exception);
        }

        abort_if($ended === null, 404);

        return $this->responses->success(
            $request,
            'Wali kelas berhasil diakhiri.',
            (new HomeroomAssignmentResource($ended))->toArray($request),
        );
    }

    private function invalidHomeroom(AssignHomeroomApiRequest|EndHomeroomApiRequest $request, KelasRombelHomeroomException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'KELAS_ROMBEL_HOMEROOM_INVALID',
            422,
            $exception->errors(),
        );
    }
}
