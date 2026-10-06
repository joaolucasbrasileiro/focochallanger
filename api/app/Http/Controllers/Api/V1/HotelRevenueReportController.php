<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RevenueReportGrouping;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\HotelRevenueReportRequest;
use App\Http\Resources\HotelRevenueReportResource;
use App\Models\Hotel;
use App\Services\Reports\HotelRevenueReportService;

class HotelRevenueReportController extends Controller
{
    public function show(
        HotelRevenueReportRequest $request,
        Hotel $hotel,
        HotelRevenueReportService $reportService,
    ): HotelRevenueReportResource {
        $this->authorize('viewFinancialReports', $hotel);
        $data = $request->validated();

        return new HotelRevenueReportResource($reportService->generate(
            $hotel,
            $data['from'],
            $data['to'],
            RevenueReportGrouping::from($data['group_by'] ?? RevenueReportGrouping::Month->value),
        ));
    }
}
