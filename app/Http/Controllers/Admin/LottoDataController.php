<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LottoDataFetcherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LottoDataController extends Controller
{
    /**
     * ดึงข้อมูลรายการหวยทั้งหมด
     */
    public function fetchAll(LottoDataFetcherService $service): JsonResponse
    {
        try {
            $result = $service->fetchAndSaveAll();
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            \Log::error('LottoDataController::fetchAll Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * ดึงข้อมูลรายการหวยจากหน้าเดียว
     */
    public function fetchSingle(Request $request, LottoDataFetcherService $service): JsonResponse
    {
        try {
            $page = (int)$request->input('page', 0);
            
            if ($page < 1 || $page > 23) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid page number. Must be between 1-23'
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            $result = $service->fetchAndSaveSingle($page);
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            \Log::error('LottoDataController::fetchSingle Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'page' => $page ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
