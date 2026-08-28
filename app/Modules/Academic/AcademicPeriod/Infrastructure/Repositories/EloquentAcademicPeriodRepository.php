<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Infrastructure\Repositories;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EloquentAcademicPeriodRepository implements AcademicPeriodRepository
{
    public function paginateYears(AcademicYearListFilter $filter): PaginatedAcademicYearData
    {
        $query = AcademicYearRecord::query()
            ->when($filter->search !== null, function ($query) use ($filter): void {
                $query->where(function ($query) use ($filter): void {
                    $query->where('name', 'like', '%'.$filter->search.'%')
                        ->orWhere('code', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->status !== null, fn ($query) => $query->where('status', $filter->status))
            ->orderBy($filter->sortField, $filter->sortDirection);

        /** @var LengthAwarePaginator<int, AcademicYearRecord> $page */
        $page = $query->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedAcademicYearData(
            data: array_map(
                fn (AcademicYearRecord $record): AcademicYearData => $this->mapYear($record),
                $page->items(),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findYear(string $id): ?AcademicYearData
    {
        $record = AcademicYearRecord::query()->find($id);

        return $record instanceof AcademicYearRecord ? $this->mapYear($record) : null;
    }

    public function createYear(UpsertAcademicYearData $data): AcademicYearData
    {
        /** @var AcademicYearRecord $record */
        $record = AcademicYearRecord::query()->create($data->toArray());

        return $this->mapYear($record);
    }

    public function updateYear(string $id, array $changes): ?AcademicYearData
    {
        $record = AcademicYearRecord::query()->find($id);

        if (! $record instanceof AcademicYearRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->mapYear($record->refresh());
    }

    public function paginateTerms(AcademicTermListFilter $filter): PaginatedAcademicTermData
    {
        $query = AcademicTermRecord::query()
            ->when($filter->search !== null, function ($query) use ($filter): void {
                $query->where(function ($query) use ($filter): void {
                    $query->where('name', 'like', '%'.$filter->search.'%')
                        ->orWhere('code', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->academicYearId !== null, fn ($query) => $query->where('academic_year_id', $filter->academicYearId))
            ->when($filter->status !== null, fn ($query) => $query->where('status', $filter->status))
            ->when($filter->isActive !== null, fn ($query) => $query->where('is_active', $filter->isActive))
            ->orderBy($filter->sortField, $filter->sortDirection);

        /** @var LengthAwarePaginator<int, AcademicTermRecord> $page */
        $page = $query->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedAcademicTermData(
            data: array_map(
                fn (AcademicTermRecord $record): AcademicTermData => $this->mapTerm($record),
                $page->items(),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findTerm(string $id): ?AcademicTermData
    {
        $record = AcademicTermRecord::query()->find($id);

        return $record instanceof AcademicTermRecord ? $this->mapTerm($record) : null;
    }

    public function currentActiveTerm(): ?AcademicTermData
    {
        $record = AcademicTermRecord::query()
            ->where('is_active', true)
            ->where('status', 'active')
            ->orderBy('starts_on')
            ->first();

        return $record instanceof AcademicTermRecord ? $this->mapTerm($record) : null;
    }

    public function createTerm(UpsertAcademicTermData $data): AcademicTermData
    {
        /** @var AcademicTermRecord $record */
        $record = AcademicTermRecord::query()->create($data->toArray());

        return $this->mapTerm($record);
    }

    public function updateTerm(string $id, array $changes): ?AcademicTermData
    {
        $record = AcademicTermRecord::query()->find($id);

        if (! $record instanceof AcademicTermRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->mapTerm($record->refresh());
    }

    public function activateTerm(string $id): ?AcademicTermData
    {
        return DB::transaction(function () use ($id): ?AcademicTermData {
            $record = AcademicTermRecord::query()->lockForUpdate()->find($id);

            if (! $record instanceof AcademicTermRecord) {
                return null;
            }

            AcademicTermRecord::query()
                ->where('id', '!=', $record->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $record->forceFill([
                'status' => 'active',
                'is_active' => true,
            ])->save();

            AcademicYearRecord::query()
                ->whereKey($record->academic_year_id)
                ->update(['status' => 'active']);

            return $this->mapTerm($record->refresh());
        });
    }

    public function closeTerm(string $id): ?AcademicTermData
    {
        return DB::transaction(function () use ($id): ?AcademicTermData {
            $record = AcademicTermRecord::query()->lockForUpdate()->find($id);

            if (! $record instanceof AcademicTermRecord) {
                return null;
            }

            $record->forceFill([
                'status' => 'closed',
                'is_active' => false,
            ])->save();

            return $this->mapTerm($record->refresh());
        });
    }

    private function mapYear(AcademicYearRecord $record): AcademicYearData
    {
        return new AcademicYearData(
            id: (string) $record->getKey(),
            code: (string) $record->code,
            name: (string) $record->name,
            startsOn: $record->starts_on->toDateString(),
            endsOn: $record->ends_on->toDateString(),
            status: (string) $record->status,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapTerm(AcademicTermRecord $record): AcademicTermData
    {
        return new AcademicTermData(
            id: (string) $record->getKey(),
            academicYearId: (string) $record->academic_year_id,
            code: (string) $record->code,
            name: (string) $record->name,
            sequence: (int) $record->sequence,
            startsOn: $record->starts_on->toDateString(),
            endsOn: $record->ends_on->toDateString(),
            status: (string) $record->status,
            isActive: (bool) $record->is_active,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }
}
