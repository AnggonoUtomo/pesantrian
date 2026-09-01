<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\Queries\ListStudents;
use App\Modules\Pesantrian\Santri\Application\Queries\ShowStudent;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ListStudentsApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentApiController implements HasMiddleware
{
    public function __construct(
        private ListStudents $listStudents,
        private ShowStudent $showStudent,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:santri.view', only: ['index', 'show']),
        ];
    }

    public function index(ListStudentsApiRequest $request): JsonResponse
    {
        $result = $this->listStudents->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar santri berhasil dibaca.',
            array_map(
                static fn (StudentData $student): array => (new StudentResource($student, includeGuardians: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $student): JsonResponse
    {
        $data = $this->showStudent->execute($student);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail santri berhasil dibaca.',
            (new StudentResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
