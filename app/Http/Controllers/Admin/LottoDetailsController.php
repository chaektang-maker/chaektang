<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LottoDetailsFetcherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LottoDetailsController extends Controller
{
    /**
     * ดึงข้อมูลหวยรายละเอียดทั้งหมดที่ยังไม่ได้ดึง
     */
    public function fetchAllPending(LottoDetailsFetcherService $service): JsonResponse
    {
        try {
            $result = $service->fetchAndSaveAllPending();
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดแบบ Batch
     */
    public function fetchBatch(Request $request, LottoDetailsFetcherService $service): JsonResponse
    {
        try {
            $limit = (int)$request->input('limit', 10);
            
            if ($limit < 1 || $limit > 100) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid limit. Must be between 1-100'
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            $result = $service->fetchAndSaveBatch($limit);
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดจาก lotto_id เดียว
     */
    public function fetchSingle(Request $request, LottoDetailsFetcherService $service): JsonResponse
    {
        try {
            $lottoId = trim($request->input('lotto_id', ''));
            
            if (empty($lottoId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid lotto_id'
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            $result = $service->fetchAndSaveSingle($lottoId);
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
