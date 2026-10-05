<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\ListImportIssuesRequest;
use App\Http\Resources\ImportIssueResource;
use App\Models\ImportIssue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ImportIssueController extends Controller
{
    public function index(ListImportIssuesRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $issues = ImportIssue::query()
            ->with('importRun')
            ->when(
                isset($filters['import_run_id']),
                fn ($query) => $query->where('import_run_id', $filters['import_run_id']),
            )
            ->when(
                isset($filters['source']),
                fn ($query) => $query->where('source', $filters['source']),
            )
            ->when(
                isset($filters['status']),
                fn ($query) => $query->where('status', $filters['status']),
            )
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);

        return ImportIssueResource::collection($issues);
    }

    public function show(ImportIssue $importIssue): ImportIssueResource
    {
        return new ImportIssueResource($importIssue->load('importRun'));
    }
}
