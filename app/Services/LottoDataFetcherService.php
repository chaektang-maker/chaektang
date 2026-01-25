<?php

namespace App\Services;

use App\Models\LottoData;
use App\Models\LottoDetail;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LottoDataFetcherService
{
    private const BASE_URL = 'https://lotto.api.rayriffy.com';
    private const LIST_ENDPOINT = '/list';
    private const DETAIL_ENDPOINT = '/lotto';

    /**
     * ดึงข้อมูลรายการหวยจาก API
     * 
     * @param int $page หมายเลขหน้า (1-23)
     * @return array|null
     */
    public function fetchFromAPI(int $page): ?array
    {
        try {
            $url = self::BASE_URL . self::LIST_ENDPOINT . '/' . $page;
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['response'])) {
                    return $data['response'];
                }
            }

            Log::warning("Failed to fetch lottery list from page {$page}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching lottery list from page {$page}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * บันทึกข้อมูลรายการหวยลง database
     * 
     * @param array $items รายการหวยจาก API
     * @return array สรุปผลการบันทึก
     */
    public function saveToDatabase(array $items): array
    {
        $inserted = 0;
        $updated = 0;
        $errors = [];

        // ตรวจสอบว่าตารางมีอยู่หรือไม่
        if (!Schema::hasTable('lotto_data')) {
            return [
                'inserted' => 0,
                'updated' => 0,
                'errors' => ['ตาราง lotto_data ยังไม่ถูกสร้าง กรุณารัน migration ก่อน']
            ];
        }

        foreach ($items as $item) {
            try {
                if (!isset($item['id']) || !isset($item['url']) || !isset($item['date'])) {
                    $errors[] = "Missing required fields in item: " . json_encode($item);
                    continue;
                }

                $lottoId = $item['id'];
                $url = $item['url'];
                $dateText = $item['date'];

                // ตรวจสอบว่ามีข้อมูลอยู่แล้วหรือไม่
                $existing = LottoData::where('lotto_id', $lottoId)->first();

                if ($existing) {
                    // อัปเดตข้อมูล
                    $existing->update([
                        'url' => $url,
                        'date_text' => $dateText,
                    ]);
                    $updated++;
                } else {
                    // เพิ่มข้อมูลใหม่ (draw_date จะถูกแปลงอัตโนมัติจาก lotto_id)
                    try {
                        $data = [
                            'lotto_id' => $lottoId,
                            'url' => $url,
                            'date_text' => $dateText,
                            'is_fetched' => false,
                        ];
                        
                        // ตรวจสอบว่ามีคอลัมน์ draw_date หรือไม่
                        if (Schema::hasColumn('lotto_data', 'draw_date')) {
                            // draw_date จะถูกแปลงอัตโนมัติจาก lotto_id ใน Model boot()
                        }
                        
                        LottoData::create($data);
                        $inserted++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        // ถ้าเป็น duplicate key error ให้ update แทน
                        if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                            $existing = LottoData::where('lotto_id', $lottoId)->first();
                            if ($existing) {
                                $existing->update([
                                    'url' => $url,
                                    'date_text' => $dateText,
                                ]);
                                $updated++;
                            }
                        } else {
                            // Log error และเพิ่มใน errors array
                            $errors[] = "Database error for lotto_id {$lottoId}: " . $e->getMessage();
                            Log::error("Database error saving lotto data", [
                                'lotto_id' => $lottoId,
                                'error' => $e->getMessage(),
                                'code' => $e->getCode()
                            ]);
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Error creating lotto_id {$lottoId}: " . $e->getMessage();
                        Log::error("Error creating lotto data", [
                            'lotto_id' => $lottoId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $lottoId = isset($item['id']) ? $item['id'] : 'unknown';
                $errors[] = "Error processing lotto_id {$lottoId}: " . $e->getMessage();
                Log::error("Error saving lotto data", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'inserted' => $inserted,
            'updated' => $updated,
            'errors' => $errors
        ];
    }

    /**
     * ดึงข้อมูลจาก API ตั้งแต่หน้า 23 ถึง 1 และบันทึกลง database
     * 
     * @return array สรุปผลการทำงาน
     */
    public function fetchAndSaveAll(): array
    {
        $totalInserted = 0;
        $totalUpdated = 0;
        $allErrors = [];
        $pageResults = [];

        // ดึงข้อมูลตั้งแต่หน้า 23 ถึง 1
        for ($page = 23; $page >= 1; $page--) {
            $result = $this->fetchFromAPI($page);

            if (!$result) {
                $pageResults[] = [
                    'page' => $page,
                    'success' => false,
                    'error' => 'Failed to fetch from API'
                ];
                continue;
            }

            $saveResult = $this->saveToDatabase($result);

            $totalInserted += $saveResult['inserted'];
            $totalUpdated += $saveResult['updated'];
            $allErrors = array_merge($allErrors, $saveResult['errors']);

            $pageResults[] = [
                'page' => $page,
                'success' => true,
                'items_count' => count($result),
                'inserted' => $saveResult['inserted'],
                'updated' => $saveResult['updated']
            ];
        }

        return [
            'success' => true,
            'total_inserted' => $totalInserted,
            'total_updated' => $totalUpdated,
            'total_errors' => count($allErrors),
            'errors' => $allErrors,
            'page_results' => $pageResults
        ];
    }

    /**
     * ดึงข้อมูลจากหน้าเดียว
     * 
     * @param int $pageNumber หมายเลขหน้า
     * @return array สรุปผลการทำงาน
     */
    public function fetchAndSaveSingle(int $pageNumber): array
    {
        $result = $this->fetchFromAPI($pageNumber);

        if (!$result) {
            return [
                'success' => false,
                'error' => 'Failed to fetch from API',
                'page' => $pageNumber
            ];
        }

        $saveResult = $this->saveToDatabase($result);

        return [
            'success' => true,
            'page' => $pageNumber,
            'items_count' => count($result),
            'inserted' => $saveResult['inserted'],
            'updated' => $saveResult['updated'],
            'errors' => $saveResult['errors']
        ];
    }
}
