<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\HumanResource\HumanResource\Application\Actions\ActivateEmployee;
use App\Modules\HumanResource\HumanResource\Application\Actions\CreateEmployee;
use App\Modules\HumanResource\HumanResource\Application\Actions\DeactivateEmployee;
use App\Modules\HumanResource\HumanResource\Application\Actions\UpdateEmployee;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;
use App\Modules\HumanResource\HumanResource\Application\DTO\PaginatedEmployeeData;
use App\Modules\HumanResource\HumanResource\Application\Queries\ListEmployees;
use App\Modules\HumanResource\HumanResource\Presentation\Requests\ActivateEmployeeApiRequest;
use App\Modules\HumanResource\HumanResource\Presentation\Requests\DeactivateEmployeeApiRequest;
use App\Modules\HumanResource\HumanResource\Presentation\Requests\ListEmployeesApiRequest;
use App\Modules\HumanResource\HumanResource\Presentation\Requests\StoreEmployeeApiRequest;
use App\Modules\HumanResource\HumanResource\Presentation\Requests\UpdateEmployeeApiRequest;
use App\Modules\HumanResource\HumanResource\Presentation\Resources\EmployeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class EmployeeApiController implements HasMiddleware
{
    public function __construct(
        private ListEmployees $listEmployees,
        private CreateEmployee $createEmployee,
        private UpdateEmployee $updateEmployee,
        private ActivateEmployee $activateEmployee,
        private DeactivateEmployee $deactivateEmployee,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:human_resource.view', only: ['index']),
            new Middleware('can:human_resource.manage', only: ['store', 'update', 'activate', 'deactivate']),
        ];
    }

    public function index(ListEmployeesApiRequest $request): JsonResponse
    {
        $result = $this->listEmployees->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar employee berhasil dibaca.',
            array_map(
                static fn (EmployeeData $employee): array => (new EmployeeResource($employee))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreEmployeeApiRequest $request): JsonResponse
    {
        $employee = $this->createEmployee->execute($request->toData());

        return $this->responses->success(
            $request,
            'Employee berhasil dibuat.',
            (new EmployeeResource($employee))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateEmployeeApiRequest $request, string $employee): JsonResponse
    {
        $updated = $this->updateEmployee->execute($employee, $request->changes());

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Employee berhasil diperbarui.',
            (new EmployeeResource($updated))->toArray($request),
        );
    }

    public function activate(ActivateEmployeeApiRequest $request, string $employee): JsonResponse
    {
        $updated = $this->activateEmployee->execute($employee);

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Employee berhasil diaktifkan.',
            (new EmployeeResource($updated))->toArray($request),
        );
    }

    public function deactivate(DeactivateEmployeeApiRequest $request, string $employee): JsonResponse
    {
        $updated = $this->deactivateEmployee->execute($employee, $request->leftOn());

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Employee berhasil dinonaktifkan.',
            (new EmployeeResource($updated))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedEmployeeData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
