<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImportRunResource;
use App\Models\ImportIssue;
use App\Models\ImportRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ImportRunController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ImportRun::class);

        return ImportRunResource::collection(
            $this->queryWithIssueCounts()
                ->orderByDesc('started_at')
                ->paginate(),
        );
    }

    public function show(ImportRun $importRun): ImportRunResource
    {
        $this->authorize('view', $importRun);

        $importRun->loadCount([
            'issues',
            'issues as reservation_issues_count' => fn (Builder $query): Builder => $query
                ->where('source', ImportIssue::SourceReservation),
        ]);

        return new ImportRunResource($importRun);
    }

    /**
     * @return Builder<ImportRun>
     */
    private function queryWithIssueCounts(): Builder
    {
        return ImportRun::query()->withCount([
            'issues',
            'issues as reservation_issues_count' => fn (Builder $query): Builder => $query
                ->where('source', ImportIssue::SourceReservation),
        ]);
    }
}
