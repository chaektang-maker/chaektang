<?php

namespace App\Services;

use App\Models\LotteryResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LottoApiService
{
    private const BASE_URL = 'https://lotto.api.rayriffy.com';
    private const LIST_ENDPOINT = '/list';
    private const DETAIL_ENDPOINT = '/lotto';

    /**
     * ดึงรายการงวดหวยจาก API
     * 
     * @param int $page หมายเลขหน้า (1-23)
     * @return array|null
     */
    public function fetchLotteryList(int $page): ?array
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
     * ดึงข้อมูลหวยแต่ละงวดจาก API
     * 
     * @param string $lotteryId ID ของงวดหวย (เช่น "01042550")
     * @return array|null
     */
    public function fetchLotteryDetail(string $lotteryId): ?array
    {
        try {
            $url = self::BASE_URL . self::DETAIL_ENDPOINT . '/' . $lotteryId;
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'success' && isset($data['response'])) {
                    return $data['response'];
                }
            }

            Log::warning("Failed to fetch lottery detail for ID {$lotteryId}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching lottery detail for ID {$lotteryId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * แปลงข้อมูลจาก API เป็นรูปแบบที่เก็บใน database
     * 
     * @param array $apiData ข้อมูลจาก API
     * @return array|null
     */
    public function parseLotteryData(array $apiData): ?array
    {
        try {
            // ดึงรางวัลที่ 1
            $firstPrize = null;
            if (isset($apiData['prizes']) && is_array($apiData['prizes'])) {
                foreach ($apiData['prizes'] as $prize) {
                    if (isset($prize['id']) && $prize['id'] === 'prizeFirst' && isset($prize['number']) && !empty($prize['number'])) {
                        $firstPrize = $prize['number'][0];
                        break;
                    }
                }
            }

            // ดึงเลขท้าย 2 ตัว และ 3 ตัว
            $lastTwoDigit = null;
            $lastThreeDigit = null;
            $runningNumbers = [];

            if (isset($apiData['runningNumbers']) && is_array($apiData['runningNumbers'])) {
                foreach ($apiData['runningNumbers'] as $running) {
                    if (isset($running['id']) && isset($running['number']) && !empty($running['number'])) {
                        switch ($running['id']) {
                            case 'runningNumberBackTwo':
                                // เลขท้าย 2 ตัว
                                if (!empty($running['number'][0])) {
                                    $lastTwoDigit = $running['number'][0];
                                }
                                break;
                            case 'runningNumberBackThree':
                                // เลขท้าย 3 ตัว
                                if (!empty($running['number'][0])) {
                                    $lastThreeDigit = $running['number'][0];
                                }
                                break;
                            case 'runningNumberFrontThree':
                                // เลขหน้า 3 ตัว (เก็บเป็น running numbers)
                                foreach ($running['number'] as $num) {
                                    if (!empty($num)) {
                                        $runningNumbers[] = $num;
                                    }
                                }
                                break;
                        }
                    }
                }
            }

            // แปลงวันที่จาก API
            $drawDate = null;
            if (isset($apiData['date'])) {
                // วันที่จาก API อาจเป็น "1 เมษายน 2550" หรือรูปแบบอื่น
                $drawDate = $this->parseThaiDate($apiData['date']);
            }

            if (!$firstPrize || !$drawDate) {
                Log::warning("Missing required data in API response", ['data' => $apiData]);
                return null;
            }

            return [
                'draw_date' => $drawDate,
                'first_prize' => $firstPrize,
                'last_two_digit' => $lastTwoDigit,
                'last_three_digit' => $lastThreeDigit,
                'running_numbers' => !empty($runningNumbers) ? $runningNumbers : null,
                'is_calculated' => false,
            ];
        } catch (\Exception $e) {
            Log::error("Error parsing lottery data: " . $e->getMessage(), ['data' => $apiData]);
            return null;
        }
    }

    /**
     * แปลงวันที่ภาษาไทยเป็นรูปแบบ YYYY-MM-DD
     * 
     * @param string $thaiDate วันที่ภาษาไทย เช่น "1 เมษายน 2550" หรือ "ลากกินแบ่งรัฐบาลงวดประจำวันที่-16-กรกฎาคม-2550"
     * @return string|null
     */
    private function parseThaiDate(string $thaiDate): ?string
    {
        try {
            // ลบคำว่า "ลากกินแบ่งรัฐบาลงวดประจำวันที่-" ถ้ามี
            $date = str_replace('ลากกินแบ่งรัฐบาลงวดประจำวันที่-', '', $thaiDate);
            $date = str_replace('-', ' ', $date);
            $date = trim($date);

            // แปลงเดือนภาษาไทยเป็นตัวเลข
            $thaiMonths = [
                'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3,
                'เมษายน' => 4, 'พฤษภาคม' => 5, 'มิถุนายน' => 6,
                'กรกฎาคม' => 7, 'สิงหาคม' => 8, 'กันยายน' => 9,
                'ตุลาคม' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12,
            ];

            $monthNum = null;
            foreach ($thaiMonths as $thaiMonth => $num) {
                if (strpos($date, $thaiMonth) !== false) {
                    $monthNum = $num;
                    $date = str_replace($thaiMonth, $num, $date);
                    break;
                }
            }

            if ($monthNum === null) {
                Log::warning("Could not find Thai month in date: {$thaiDate}");
                return null;
            }

            // แยกวันที่ เดือน ปี
            $parts = array_filter(explode(' ', trim($date)));
            $parts = array_values($parts); // reindex array
            
            if (count($parts) >= 3) {
                $day = (int)$parts[0];
                $month = (int)$parts[1];
                $year = (int)$parts[2];

                // แปลงปี พ.ศ. เป็น ค.ศ.
                $year = $year - 543;

                // สร้าง Carbon date
                $carbonDate = Carbon::create($year, $month, $day);
                return $carbonDate->format('Y-m-d');
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error parsing Thai date: {$thaiDate} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * บันทึกข้อมูลหวยลง database
     * 
     * @param array $lotteryData ข้อมูลหวยที่แปลงแล้ว
     * @return LotteryResult|null
     */
    public function saveLotteryResult(array $lotteryData): ?LotteryResult
    {
        try {
            // ตรวจสอบว่ามีข้อมูลอยู่แล้วหรือไม่
            $existing = LotteryResult::where('draw_date', $lotteryData['draw_date'])->first();
            
            if ($existing) {
                // อัพเดทข้อมูลที่มีอยู่
                $existing->update($lotteryData);
                return $existing;
            }

            // สร้างข้อมูลใหม่
            return LotteryResult::create($lotteryData);
        } catch (\Exception $e) {
            Log::error("Error saving lottery result: " . $e->getMessage(), ['data' => $lotteryData]);
            return null;
        }
    }

    /**
     * ดึงและบันทึกข้อมูลหวยย้อนหลังทั้งหมด (หน้า 1-23)
     * 
     * @param int $startPage หน้าเริ่มต้น (default: 23)
     * @param int $endPage หน้าสุดท้าย (default: 1)
     * @return array สรุปผลการทำงาน
     */
    public function importHistoricalLotteries(int $startPage = 23, int $endPage = 1): array
    {
        $summary = [
            'total_pages' => 0,
            'total_lotteries' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // วนลูปจากหน้า 23 ถึง 1
        for ($page = $startPage; $page >= $endPage; $page--) {
            $summary['total_pages']++;
            
            Log::info("Fetching lottery list from page {$page}");
            $lotteryList = $this->fetchLotteryList($page);

            if (!$lotteryList || !is_array($lotteryList)) {
                $summary['failed']++;
                $summary['errors'][] = "Failed to fetch list from page {$page}";
                continue;
            }

            foreach ($lotteryList as $lottery) {
                $summary['total_lotteries']++;
                
                if (!isset($lottery['id']) || !isset($lottery['url'])) {
                    $summary['skipped']++;
                    continue;
                }

                $lotteryId = $lottery['id'];
                Log::info("Fetching lottery detail for ID: {$lotteryId}");

                // ดึงข้อมูลหวยแต่ละงวด
                $detailData = $this->fetchLotteryDetail($lotteryId);
                
                if (!$detailData) {
                    $summary['failed']++;
                    $summary['errors'][] = "Failed to fetch detail for ID: {$lotteryId}";
                    continue;
                }

                // แปลงข้อมูล
                $parsedData = $this->parseLotteryData($detailData);
                
                if (!$parsedData) {
                    $summary['failed']++;
                    $summary['errors'][] = "Failed to parse data for ID: {$lotteryId}";
                    continue;
                }

                // บันทึกลง database
                $result = $this->saveLotteryResult($parsedData);
                
                if ($result) {
                    $summary['success']++;
                    Log::info("Successfully saved lottery result for date: {$parsedData['draw_date']}");
                } else {
                    $summary['failed']++;
                    $summary['errors'][] = "Failed to save result for ID: {$lotteryId}";
                }

                // หน่วงเวลาเล็กน้อยเพื่อไม่ให้ API rate limit
                usleep(500000); // 0.5 วินาที
            }
        }

        return $summary;
    }
}
