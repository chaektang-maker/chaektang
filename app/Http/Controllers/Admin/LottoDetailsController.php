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

    /**
     * บันทึกผลรางวัลหลักด้วยตนเอง (กรณี API กองสลากล่ม 503 หรือทดสอบงวดหวย)
     */
    public function saveManual(Request $request, LottoDetailsFetcherService $service): JsonResponse
    {
        $validated = $request->validate([
            'lotto_id' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
            'first_prize' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'front_three' => ['nullable', 'string', 'max:50'],
            'back_three' => ['nullable', 'string', 'max:50'],
            'back_two' => ['required', 'string', 'regex:/^[0-9]{2}$/'],
        ], [
            'lotto_id.regex' => 'รหัสงวดต้องเป็นตัวเลข 8 หลัก (เช่น 16112569)',
            'first_prize.regex' => 'รางวัลที่ 1 ต้องเป็นตัวเลข 6 หลัก',
            'back_two.regex' => 'เลขท้าย 2 ตัวต้องเป็นตัวเลข 2 หลัก',
        ]);

        try {
            $result = $service->saveManualPrizes($validated['lotto_id'], $validated);
            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
