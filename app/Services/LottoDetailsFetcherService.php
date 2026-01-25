<?php

namespace App\Services;

use App\Models\LottoData;
use App\Models\LottoDetail;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LottoDetailsFetcherService
{
    private const BASE_URL = 'https://lotto.api.rayriffy.com';
    private const DETAIL_ENDPOINT = '/lotto';

    /**
     * ดึงข้อมูลหวยรายละเอียดจาก API
     * 
     * @param string $lottoId ID ของหวย
     * @return array|null
     */
    public function fetchDetailsFromAPI(string $lottoId): ?array
    {
        try {
            $url = self::BASE_URL . self::DETAIL_ENDPOINT . '/' . $lottoId;
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['response'])) {
                    return $data['response'];
                }
            }

            Log::warning("Failed to fetch lottery detail for ID {$lottoId}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching lottery detail for ID {$lottoId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * บันทึกข้อมูลหวยรายละเอียดลง database
     * 
     * @param string $lottoId ID ของหวย
     * @param array $details ข้อมูลรายละเอียดจาก API
     * @return array สรุปผลการบันทึก
     */
    public function saveDetailsToDatabase(string $lottoId, array $details): array
    {
        DB::beginTransaction();

        try {
            // บันทึกหรืออัปเดตข้อมูลหลัก (lotto_details)
            $date = $details['date'] ?? '';
            $endpoint = $details['endpoint'] ?? '';

            LottoDetail::updateOrCreate(
                ['lotto_id' => $lottoId],
                [
                    'date' => $date,
                    'endpoint' => $endpoint,
                ]
            );

            // ลบรางวัลเก่าถ้ามี
            LottoPrize::where('lotto_id', $lottoId)->delete();

            // บันทึกรางวัล
            if (isset($details['prizes']) && is_array($details['prizes'])) {
                foreach ($details['prizes'] as $prize) {
                    LottoPrize::create([
                        'lotto_id' => $lottoId,
                        'prize_id' => $prize['id'] ?? '',
                        'prize_name' => $prize['name'] ?? '',
                        'reward' => $prize['reward'] ?? '',
                        'amount' => intval($prize['amount'] ?? 0),
                        'numbers' => $prize['number'] ?? [],
                    ]);
                }
            }

            // ลบเลขวิ่งเก่าถ้ามี
            LottoRunningNumber::where('lotto_id', $lottoId)->delete();

            // บันทึกเลขวิ่ง
            if (isset($details['runningNumbers']) && is_array($details['runningNumbers'])) {
                foreach ($details['runningNumbers'] as $running) {
                    LottoRunningNumber::create([
                        'lotto_id' => $lottoId,
                        'running_id' => $running['id'] ?? '',
                        'running_name' => $running['name'] ?? '',
                        'reward' => $running['reward'] ?? '',
                        'amount' => intval($running['amount'] ?? 0),
                        'numbers' => $running['number'] ?? [],
                    ]);
                }
            }

            // อัปเดตสถานะ is_fetched ใน lotto_data
            LottoData::where('lotto_id', $lottoId)->update(['is_fetched' => true]);

            DB::commit();

            return [
                'success' => true,
                'prizes_count' => isset($details['prizes']) ? count($details['prizes']) : 0,
                'running_numbers_count' => isset($details['runningNumbers']) ? count($details['runningNumbers']) : 0
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving lotto details for ID {$lottoId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดจาก lotto_id เดียว
     * 
     * @param string $lottoId ID ของหวย
     * @return array สรุปผลการทำงาน
     */
    public function fetchAndSaveSingle(string $lottoId): array
    {
        $result = $this->fetchDetailsFromAPI($lottoId);

        if (!$result) {
            return [
                'success' => false,
                'error' => 'Failed to fetch from API',
                'lotto_id' => $lottoId
            ];
        }

        $saveResult = $this->saveDetailsToDatabase($lottoId, $result);

        if ($saveResult['success']) {
            return [
                'success' => true,
                'lotto_id' => $lottoId,
                'date' => $result['date'] ?? '',
                'prizes_count' => $saveResult['prizes_count'],
                'running_numbers_count' => $saveResult['running_numbers_count']
            ];
        } else {
            return [
                'success' => false,
                'error' => $saveResult['error'],
                'lotto_id' => $lottoId
            ];
        }
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดทั้งหมดที่ยังไม่ได้ดึง (is_fetched = 0)
     * 
     * @return array สรุปผลการทำงาน
     */
    public function fetchAndSaveAllPending(): array
    {
        $totalPending = LottoData::where('is_fetched', false)->count();

        if ($totalPending == 0) {
            return [
                'success' => true,
                'message' => 'ไม่มีข้อมูลที่ต้องดึง (ทุกรายการดึงข้อมูลแล้ว)',
                'total_pending' => 0,
                'total_processed' => 0,
                'total_success' => 0,
                'total_failed' => 0,
                'results' => []
            ];
        }

        $lottoIds = LottoData::where('is_fetched', false)
            ->orderBy('lotto_id', 'desc')
            ->pluck('lotto_id')
            ->toArray();

        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $results = [];

        foreach ($lottoIds as $lottoId) {
            $totalProcessed++;
            
            $fetchResult = $this->fetchAndSaveSingle($lottoId);
            $results[] = $fetchResult;

            if ($fetchResult['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }

            // หน่วงเวลาเล็กน้อยเพื่อไม่ให้โหลด API หนักเกินไป
            usleep(500000); // 0.5 วินาที
        }

        return [
            'success' => true,
            'total_pending' => $totalPending,
            'total_processed' => $totalProcessed,
            'total_success' => $totalSuccess,
            'total_failed' => $totalFailed,
            'results' => $results
        ];
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดตามจำนวนที่กำหนด (เฉพาะ is_fetched = 0)
     * 
     * @param int $limit จำนวนรายการที่ต้องการดึง
     * @return array สรุปผลการทำงาน
     */
    public function fetchAndSaveBatch(int $limit = 10): array
    {
        $limit = max(1, min(100, $limit)); // จำกัดระหว่าง 1-100

        $totalPending = LottoData::where('is_fetched', false)->count();

        if ($totalPending == 0) {
            return [
                'success' => true,
                'message' => 'ไม่มีข้อมูลที่ต้องดึง (ทุกรายการดึงข้อมูลแล้ว)',
                'total_pending' => 0,
                'total_processed' => 0,
                'total_success' => 0,
                'total_failed' => 0,
                'results' => []
            ];
        }

        $lottoIds = LottoData::where('is_fetched', false)
            ->orderBy('lotto_id', 'desc')
            ->limit($limit)
            ->pluck('lotto_id')
            ->toArray();

        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $results = [];

        foreach ($lottoIds as $lottoId) {
            $totalProcessed++;
            
            $fetchResult = $this->fetchAndSaveSingle($lottoId);
            $results[] = $fetchResult;

            if ($fetchResult['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }

            // หน่วงเวลาเล็กน้อยเพื่อไม่ให้โหลด API หนักเกินไป
            usleep(500000); // 0.5 วินาที
        }

        return [
            'success' => true,
            'total_pending' => $totalPending,
            'total_processed' => $totalProcessed,
            'total_success' => $totalSuccess,
            'total_failed' => $totalFailed,
            'results' => $results
        ];
    }
}
