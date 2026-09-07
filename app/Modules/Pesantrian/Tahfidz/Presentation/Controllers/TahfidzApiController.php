<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzProgram;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzTarget;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzProgram;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzTarget;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ListTahfidzSubmissions;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ShowTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\ListTahfidzSubmissionsApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzProgramApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzSubmissionApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzTargetApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzProgramApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzSubmissionApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzTargetApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzProgramResource;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzSubmissionResource;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzTargetResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class TahfidzApiController implements HasMiddleware
{
    public function __construct(
        private ListTahfidzSubmissions $listTahfidzSubmissions,
        private ShowTahfidzSubmission $showTahfidzSubmission,
        private CreateTahfidzProgram $createTahfidzProgram,
        private UpdateTahfidzProgram $updateTahfidzProgram,
        private CreateTahfidzTarget $createTahfidzTarget,
        private UpdateTahfidzTarget $updateTahfidzTarget,
        private CreateTahfidzSubmission $createTahfidzSubmission,
        private UpdateTahfidzSubmission $updateTahfidzSubmission,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:tahfidz.view', only: ['index', 'show']),
            new Middleware('can:tahfidz.manage', only: ['storeProgram', 'updateProgram', 'storeTarget', 'updateTarget']),
            new Middleware('can:tahfidz.record', only: ['storeSubmission', 'updateSubmission']),
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

    public function storeProgram(StoreTahfidzProgramApiRequest $request): JsonResponse
    {
        $program = $this->createTahfidzProgram->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Program tahfidz berhasil dibuat.',
            (new TahfidzProgramResource($program))->toArray($request),
            status: 201,
        );
    }

    public function updateProgram(UpdateTahfidzProgramApiRequest $request, string $program): JsonResponse
    {
        $updated = $this->updateTahfidzProgram->execute(
            $request->user(),
            $program,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Program tahfidz berhasil diperbarui.',
            (new TahfidzProgramResource($updated))->toArray($request),
        );
    }

    public function storeTarget(StoreTahfidzTargetApiRequest $request): JsonResponse
    {
        try {
            $target = $this->createTahfidzTarget->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Target hafalan berhasil dibuat.',
            (new TahfidzTargetResource($target))->toArray($request),
            status: 201,
        );
    }

    public function updateTarget(UpdateTahfidzTargetApiRequest $request, string $target): JsonResponse
    {
        try {
            $updated = $this->updateTahfidzTarget->execute(
                $request->user(),
                $target,
                $request->changes(),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Target hafalan berhasil diperbarui.',
            (new TahfidzTargetResource($updated))->toArray($request),
        );
    }

    public function storeSubmission(StoreTahfidzSubmissionApiRequest $request): JsonResponse
    {
        try {
            $submission = $this->createTahfidzSubmission->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Setoran tahfidz berhasil dibuat.',
            (new TahfidzSubmissionResource($submission))->toArray($request),
            status: 201,
        );
    }

    public function updateSubmission(UpdateTahfidzSubmissionApiRequest $request, string $submission): JsonResponse
    {
        try {
            $updated = $this->updateTahfidzSubmission->execute(
                $request->user(),
                $submission,
                $request->changes(),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Setoran tahfidz berhasil diperbarui.',
            (new TahfidzSubmissionResource($updated))->toArray($request),
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

    private function invalidMutation(Request $request, TahfidzMutationException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'TAHFIDZ_MUTATION_INVALID',
            422,
            $exception->errors(),
        );
    }
}
