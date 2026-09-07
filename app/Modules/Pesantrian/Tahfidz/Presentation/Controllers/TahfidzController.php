<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Controllers;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ListTahfidzSubmissions;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ShowTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\ListTahfidzSubmissionsApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzSubmissionResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TahfidzController implements HasMiddleware
{
    public function __construct(
        private ListTahfidzSubmissions $listTahfidzSubmissions,
        private ShowTahfidzSubmission $showTahfidzSubmission,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:tahfidz.view', only: ['index', 'show']),
        ];
    }

    public function index(ListTahfidzSubmissionsApiRequest $request): Response
    {
        $result = $this->listTahfidzSubmissions->execute($request->toFilter());

        return Inertia::render('Pesantrian/Tahfidz/pages/Index', [
            'submissions' => [
                'data' => array_map(
                    static fn (TahfidzSubmissionData $submission): array => (new TahfidzSubmissionResource($submission, includeRevisions: false))->toArray($request),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'options' => $this->options(),
            'canManage' => $request->user()?->can('tahfidz.manage') === true,
            'canRecord' => $request->user()?->can('tahfidz.record') === true,
            'canReview' => $request->user()?->can('tahfidz.review') === true,
            'canArchive' => $request->user()?->can('tahfidz.archive') === true,
        ]);
    }

    public function show(Request $request, string $submission): Response
    {
        $data = $this->showTahfidzSubmission->execute($submission);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/Tahfidz/pages/Show', [
            'submission' => (new TahfidzSubmissionResource($data))->toArray($request),
            'options' => $this->options(),
            'canManage' => $request->user()?->can('tahfidz.manage') === true,
            'canRecord' => $request->user()?->can('tahfidz.record') === true,
            'canReview' => $request->user()?->can('tahfidz.review') === true,
            'canArchive' => $request->user()?->can('tahfidz.archive') === true,
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedTahfidzSubmissionData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array{types: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>} */
    private function options(): array
    {
        return [
            'types' => [
                ['value' => 'new_memorization', 'label' => 'Hafalan baru'],
                ['value' => 'murojaah', 'label' => 'Murojaah'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'submitted', 'label' => 'Menunggu review'],
                ['value' => 'accepted', 'label' => 'Diterima'],
                ['value' => 'needs_revision', 'label' => 'Perlu koreksi'],
                ['value' => 'void', 'label' => 'Dibatalkan'],
            ],
        ];
    }
}
