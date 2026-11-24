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
    private string $lastError = 'Failed to fetch from API';

    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * ดึงข้อมูลหวยรายละเอียดจาก API (มีระบบสำรองอัตโนมัติ: Rayriffy API -> GLO Official API)
     *
     * @param string $lottoId ID ของหวย (DDMMYYYY พ.ศ.)
     * @return array|null
     */
    public function fetchDetailsFromAPI(string $lottoId): ?array
    {
        if (!preg_match('/^[0-9]{8}$/', $lottoId)) {
            $this->lastError = "รหัสงวด {$lottoId} ไม่ถูกต้อง (ต้องเป็นตัวเลข 8 หลัก วันเดือนปี พ.ศ.)";
            Log::warning("Invalid lottoId format for fetch: {$lottoId}");
            return null;
        }

        // 1. ลองดึงจาก Rayriffy API ก่อน (รวดเร็วและไม่ติดบล็อก 503 ของกองสลาก)
        try {
            $rayUrl = self::BASE_URL . self::DETAIL_ENDPOINT . '/' . $lottoId;
            $rayResponse = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])
                ->get($rayUrl);

            if ($rayResponse->successful()) {
                $rayData = $rayResponse->json();
                $resp = $rayData['response'] ?? null;
                if (($rayData['status'] ?? '') === 'success' && is_array($resp) && !empty($resp['prizes'])) {
                    $firstPrizeNum = $resp['prizes'][0]['number'][0] ?? '';
                    if ($firstPrizeNum !== '' && $firstPrizeNum !== 'xxxxxx' && !str_contains($firstPrizeNum, 'x')) {
                        return $resp;
                    }
                    $this->lastError = "งวด {$lottoId} ยังไม่ประกาศผลรางวัลในระบบต้นทาง (ผลรางวัลยังเป็น xxxxxx)";
                }
            }
        } catch (\Throwable $e) {
            Log::info("Rayriffy fallback notice for {$lottoId}: " . $e->getMessage());
        }

        // 2. ลองดึงจาก GLO Official API พร้อม Browser Headers
        try {
            $day = substr($lottoId, 0, 2);
            $month = substr($lottoId, 2, 2);
            $yearBE = (int) substr($lottoId, 4, 4);
            $yearAD = $yearBE - 543;

            $payload = [
                'date' => $day,
                'month' => $month,
                'year' => (string) $yearAD,
            ];

            $response = Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json, text/plain, */*',
                    'Origin' => 'https://www.glo.or.th',
                    'Referer' => 'https://www.glo.or.th/',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                ])
                ->post('https://www.glo.or.th/api/checking/getLotteryResult', $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === true && !empty($data['response']['result']['data'])) {
                    $parsed = $this->parseGloResultToLegacyFormat($data['response'], $lottoId);
                    if (!empty($parsed['prizes'])) {
                        return $parsed;
                    }
                }

                $this->lastError = "ไม่พบข้อมูลผลรางวัลงวด {$lottoId} ในระบบกองสลาก (อาจยังไม่ถึงวันประกาศผลของงวดนี้) — คุณสามารถกดปุ่ม '✍️ กรอกผลรางวัล' เพื่อบันทึกผลด้วยตนเองได้ทันที";
                return null;
            }

            if ($response->status() === 503) {
                $this->lastError = "เซิร์ฟเวอร์กองสลาก (glo.or.th) ตอบกลับ 503 (เซิร์ฟเวอร์ต้นทางหนาแน่นหรือยังไม่มีข้อมูลงวด {$lottoId}) — คุณสามารถกดปุ่ม '✍️ กรอกผลรางวัล' ในตารางด้านล่างเพื่อบันทึกผลรางวัลด้วยตนเองได้ทันที";
            } else {
                $this->lastError = "API ต้นทางตอบกลับสถานะ HTTP {$response->status()} สำหรับงวด {$lottoId} — สามารถใช้ปุ่ม '✍️ กรอกผลรางวัล' แทนได้";
            }

            Log::warning("Failed to fetch lottery detail for ID {$lottoId} from GLO API", [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500),
            ]);

            return null;
        } catch (\Exception $e) {
            $this->lastError = "ไม่สามารถเชื่อมต่อ API ต้นทางได้ ({$e->getMessage()}) — สามารถใช้ปุ่ม '✍️ กรอกผลรางวัล' แทนได้";
            Log::error("Error fetching lottery detail for ID {$lottoId} from GLO API: " . $e->getMessage());
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

            // ซิงค์ผลหวยลง lottery_results และคำนวณความแม่นยำอัตโนมัติ
            try {
                app(AccuracyCalculationService::class)->syncFromLottoDetail($lottoId, true);
            } catch (\Exception $calcException) {
                Log::warning("Could not sync/calculate accuracy for lotto_id {$lottoId}: " . $calcException->getMessage());
            }

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
                'error' => $this->lastError ?: 'Failed to fetch from API',
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
     * บันทึกผลรางวัลหลักแบบกรอกเอง (กรณี API กองสลากล่มในวันหวยออก หรือใช้ทดสอบงวดล่วงหน้า)
     */
    public function saveManualPrizes(string $lottoId, array $input): array
    {
        $firstPrize = str_pad(preg_replace('/\D/', '', (string) ($input['first_prize'] ?? '')), 6, '0', STR_PAD_LEFT);
        $backTwo = str_pad(preg_replace('/\D/', '', (string) ($input['back_two'] ?? '')), 2, '0', STR_PAD_LEFT);

        $parseThreeDigits = function ($raw): array {
            if (is_array($raw)) {
                $parts = $raw;
            } else {
                $parts = preg_split('/[\s,]+/', trim((string) $raw)) ?: [];
            }
            $cleaned = [];
            foreach ($parts as $p) {
                $d = preg_replace('/\D/', '', (string) $p);
                if ($d !== '') {
                    $cleaned[] = str_pad(substr($d, 0, 3), 3, '0', STR_PAD_LEFT);
                }
            }
            return array_values(array_slice($cleaned, 0, 2));
        };

        $frontThree = $parseThreeDigits($input['front_three'] ?? []);
        $backThree = $parseThreeDigits($input['back_three'] ?? []);

        // คำนวณรางวัลข้างเคียงรางวัลที่ 1 อัตโนมัติ
        $firstInt = (int) $firstPrize;
        $nearLow = str_pad((string) (($firstInt - 1 + 1000000) % 1000000), 6, '0', STR_PAD_LEFT);
        $nearHigh = str_pad((string) (($firstInt + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $lottoData = LottoData::where('lotto_id', $lottoId)->first();
        $day = substr($lottoId, 0, 2);
        $month = substr($lottoId, 2, 2);
        $yearBE = (int) substr($lottoId, 4, 4);
        $yearAD = $yearBE > 2400 ? $yearBE - 543 : $yearBE;
        $dateStr = sprintf('%04d-%02d-%02d', $yearAD, (int) $month, (int) $day);
        $thaiDate = $lottoData?->date_text ?: $this->formatToThaiDate($dateStr);

        if (!$lottoData) {
            LottoData::create([
                'lotto_id' => $lottoId,
                'url' => 'https://www.glo.or.th',
                'date_text' => $thaiDate,
                'draw_date' => $dateStr,
                'is_fetched' => true,
            ]);
        }

        $details = [
            'date' => $thaiDate,
            'endpoint' => 'manual-entry',
            'prizes' => [
                [
                    'id' => 'prizeFirst',
                    'name' => 'รางวัลที่ 1',
                    'reward' => '6000000',
                    'amount' => 1,
                    'number' => [$firstPrize],
                ],
                [
                    'id' => 'prizeFirstNear',
                    'name' => 'รางวัลข้างเคียงรางวัลที่ 1',
                    'reward' => '100000',
                    'amount' => 2,
                    'number' => [$nearLow, $nearHigh],
                ],
            ],
            'runningNumbers' => array_values(array_filter([
                !empty($frontThree) ? [
                    'id' => 'runningNumberFrontThree',
                    'name' => 'รางวัลเลขหน้า 3 ตัว',
                    'reward' => '4000',
                    'amount' => count($frontThree),
                    'number' => $frontThree,
                ] : null,
                !empty($backThree) ? [
                    'id' => 'runningNumberBackThree',
                    'name' => 'รางวัลเลขท้าย 3 ตัว',
                    'reward' => '4000',
                    'amount' => count($backThree),
                    'number' => $backThree,
                ] : null,
                [
                    'id' => 'runningNumberBackTwo',
                    'name' => 'รางวัลเลขท้าย 2 ตัว',
                    'reward' => '2000',
                    'amount' => 1,
                    'number' => [$backTwo],
                ],
            ])),
        ];

        $saveResult = $this->saveDetailsToDatabase($lottoId, $details);

        if ($saveResult['success']) {
            return [
                'success' => true,
                'lotto_id' => $lottoId,
                'date' => $thaiDate,
                'prizes_count' => $saveResult['prizes_count'],
                'running_numbers_count' => $saveResult['running_numbers_count'],
            ];
        }

        return [
            'success' => false,
            'error' => $saveResult['error'] ?? 'ไม่สามารถบันทึกข้อมูลได้',
            'lotto_id' => $lottoId,
        ];
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

    /**
     * แปลงข้อมูลจาก GLO API ไปเป็นโครงสร้างรูปแบบเดิมเพื่อบันทึกลง Database
     */
    private function parseGloResultToLegacyFormat(array $gloResponse, string $lottoId): array
    {
        $result = $gloResponse['result'] ?? null;
        if (!$result) {
            return [];
        }

        $dateStr = $result['date'] ?? ''; // e.g. "2010-03-01"
        $formattedDate = $this->formatToThaiDate($dateStr);

        $gloPrizes = $result['data'] ?? [];
        $prizes = [];
        $runningNumbers = [];

        // 1. แผนผังการแปลงรางวัล (lotto_prizes)
        $prizeMapping = [
            'first' => ['id' => 'prizeFirst', 'name' => 'รางวัลที่ 1'],
            'near1' => ['id' => 'prizeFirstNear', 'name' => 'รางวัลข้างเคียงรางวัลที่ 1'],
            'second' => ['id' => 'prizeSecond', 'name' => 'รางวัลที่ 2'],
            'third' => ['id' => 'prizeThird', 'name' => 'รางวัลที่ 3'],
            'fourth' => ['id' => 'prizeForth', 'name' => 'รางวัลที่ 4'],
            'fifth' => ['id' => 'prizeFifth', 'name' => 'รางวัลที่ 5'],
        ];

        foreach ($prizeMapping as $gloKey => $meta) {
            if (isset($gloPrizes[$gloKey])) {
                $item = $gloPrizes[$gloKey];
                $prizes[] = [
                    'id' => $meta['id'],
                    'name' => $meta['name'],
                    'reward' => (string)intval($item['price'] ?? 0),
                    'amount' => count($item['number'] ?? []),
                    'number' => array_column($item['number'] ?? [], 'value')
                ];
            }
        }

        // 2. แผนผังการแปลงเลขวิ่ง (lotto_running_numbers)
        $runningMapping = [
            'last3f' => ['id' => 'runningNumberFrontThree', 'name' => 'รางวัลเลขหน้า 3 ตัว'],
            'last3b' => ['id' => 'runningNumberBackThree', 'name' => 'รางวัลเลขท้าย 3 ตัว'],
            'last2' => ['id' => 'runningNumberBackTwo', 'name' => 'รางวัลเลขท้าย 2 ตัว'],
        ];

        foreach ($runningMapping as $gloKey => $meta) {
            if (isset($gloPrizes[$gloKey])) {
                $item = $gloPrizes[$gloKey];
                if (!empty($item['number'])) {
                    $runningNumbers[] = [
                        'id' => $meta['id'],
                        'name' => $meta['name'],
                        'reward' => (string)intval($item['price'] ?? 0),
                        'amount' => count($item['number'] ?? []),
                        'number' => array_column($item['number'] ?? [], 'value')
                    ];
                }
            }
        }

        return [
            'date' => $formattedDate,
            'endpoint' => 'https://www.glo.or.th/api/checking/getLotteryResult',
            'prizes' => $prizes,
            'runningNumbers' => $runningNumbers
        ];
    }

    /**
     * ช่วยแปลงวันที่ YYYY-MM-DD เป็นไทยเท็กซ์ เช่น "1 มีนาคม 2553"
     */
    private function formatToThaiDate(string $dateStr): string
    {
        if (empty($dateStr)) return '';
        try {
            $time = strtotime($dateStr);
            $day = (int)date('d', $time);
            $month = (int)date('m', $time);
            $yearBE = (int)date('Y', $time) + 543;
            
            $thaiMonths = [
                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
                4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
                7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
                10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
            ];
            
            return $day . ' ' . ($thaiMonths[$month] ?? '') . ' ' . $yearBE;
        } catch (\Exception $e) {
            return $dateStr;
        }
    }
}
