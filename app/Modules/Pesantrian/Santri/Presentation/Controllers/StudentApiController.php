<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Santri\Application\Actions\ArchiveStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\ChangeStudentLifecycle;
use App\Modules\Pesantrian\Santri\Application\Actions\CreateStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\CreateStudentFromAcceptedAdmission;
use App\Modules\Pesantrian\Santri\Application\Actions\RestoreStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\UpdateStudent;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\Queries\ListStudents;
use App\Modules\Pesantrian\Santri\Application\Queries\ShowStudent;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ArchiveStudentApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ChangeStudentLifecycleApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ListStudentsApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\StoreStudentApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\UpdateStudentApiRequest;
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
        private CreateStudent $createStudent,
        private CreateStudentFromAcceptedAdmission $createStudentFromAcceptedAdmission,
        private ChangeStudentLifecycle $changeStudentLifecycle,
        private ArchiveStudent $archiveStudent,
        private RestoreStudent $restoreStudent,
        private UpdateStudent $updateStudent,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:santri.view', only: ['index', 'show']),
            new Middleware('can:santri.manage', only: ['store', 'storeFromAdmission', 'update']),
            new Middleware('can:santri.lifecycle', only: ['lifecycle']),
            new Middleware('can:santri.archive', only: ['archive', 'restore']),
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

    public function store(StoreStudentApiRequest $request): JsonResponse
    {
        $student = $this->createStudent->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Data santri berhasil dibuat.',
            (new StudentResource($student))->toArray($request),
            status: 201,
        );
    }

    public function storeFromAdmission(Request $request, string $admission): JsonResponse
    {
        $student = $this->createStudentFromAcceptedAdmission->execute(
            $request->user(),
            $admission,
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Pendaftaran diterima berhasil dikonversi menjadi santri.',
            (new StudentResource($student))->toArray($request),
            status: 201,
        );
    }

    public function lifecycle(ChangeStudentLifecycleApiRequest $request, string $student): JsonResponse
    {
        $updated = $this->changeStudentLifecycle->execute(
            $request->user(),
            $student,
            $request->status(),
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Status santri berhasil diperbarui.',
            (new StudentResource($updated))->toArray($request),
        );
    }

    public function archive(ArchiveStudentApiRequest $request, string $student): JsonResponse
    {
        $archived = $this->archiveStudent->execute(
            $request->user(),
            $student,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        return $this->responses->success(
            $request,
            'Data santri berhasil diarsipkan.',
            (new StudentResource($archived))->toArray($request),
        );
    }

    public function restore(ArchiveStudentApiRequest $request, string $student): JsonResponse
    {
        $restored = $this->restoreStudent->execute(
            $request->user(),
            $student,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($restored === null, 404);

        return $this->responses->success(
            $request,
            'Data santri berhasil dipulihkan.',
            (new StudentResource($restored))->toArray($request),
        );
    }

    public function update(UpdateStudentApiRequest $request, string $student): JsonResponse
    {
        $updated = $this->updateStudent->execute(
            $request->user(),
            $student,
            $request->studentChanges(),
            $request->guardianChanges(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Data santri berhasil diperbarui.',
            (new StudentResource($updated))->toArray($request),
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
