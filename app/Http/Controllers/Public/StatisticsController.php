<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\LottoStatisticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    protected $statisticsService;

    public function __construct(LottoStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * แสดงหน้าสถิติหวย
     */
    public function index(Request $request)
    {
        $startYear = $request->input('start_year', 2552);
        $endYear = $request->input('end_year', (int)date('Y') + 543);
        $type = $request->input('type', 'last_two');
        $limit = $request->input('limit', 20);

        // ดึงสถิติทั้งหมด
        $allStatistics = $this->statisticsService->getAllStatistics();

        // ดึงสถิติตามช่วงปี (ถ้ามีการระบุ)
        $yearRangeStatistics = null;
        if ($startYear && $endYear) {
            $yearRangeStatistics = $this->statisticsService->getStatisticsByYearRange(
                $startYear,
                $endYear,
                $type,
                $limit
            );
        }

        return Inertia::render('Statistics/Index', [
            'allStatistics' => $allStatistics,
            'yearRangeStatistics' => $yearRangeStatistics,
            'filters' => [
                'start_year' => $startYear,
                'end_year' => $endYear,
                'type' => $type,
                'limit' => $limit,
            ],
        ]);
    }
}
