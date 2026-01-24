<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LottoData;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลผลหวยล่าสุด
        $latestLotto = LottoData::query()
            ->where('is_fetched', 1)
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->first();

        $latestResult = null;
        if ($latestLotto) {
            // ดึงรางวัลที่ 1
            $firstPrize = LottoPrize::where('lotto_id', $latestLotto->lotto_id)
                ->where('prize_id', 'prizeFirst')
                ->first();

            // ดึงเลขท้าย 2 ตัว
            $lastTwoDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                ->where('running_id', 'runningNumberBackTwo')
                ->first();

            // ดึงเลขหน้า 3 ตัว
            $frontThreeDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                ->where('running_id', 'runningNumberFrontThree')
                ->first();

            // ดึงเลขท้าย 3 ตัว
            $lastThreeDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                ->where('running_id', 'runningNumberBackThree')
                ->first();

            // ดึงรางวัลเลขข้างเคียงรางวัลที่ 1
            $nearbyPrize = LottoPrize::where('lotto_id', $latestLotto->lotto_id)
                ->where('prize_id', 'prizeFirstNear')
                ->first();

            // ดึงรางวัลที่ 2
            $secondPrize = LottoPrize::where('lotto_id', $latestLotto->lotto_id)
                ->where('prize_id', 'prizeSecond')
                ->first();

            $latestResult = [
                'lotto_id' => $latestLotto->lotto_id,
                'draw_date' => $latestLotto->draw_date?->format('Y-m-d'),
                'date_text' => $this->formatDateText($latestLotto->date_text),
                'first_prize' => $firstPrize?->numbers[0] ?? null,
                'last_two_digit' => $lastTwoDigit?->numbers[0] ?? null,
                'front_three_digit' => $frontThreeDigit?->numbers ?? [],
                'last_three_digit' => $lastThreeDigit?->numbers ?? [],
                'nearby_prizes' => $nearbyPrize?->numbers ?? [],
                'second_prizes' => $secondPrize?->numbers ?? [],
            ];
        }

        // ดึงรายการงวดทั้งหมดสำหรับ dropdown
        $availableDraws = LottoData::query()
            ->where('is_fetched', 1)
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->lotto_id,
                    'label' => $this->formatDateText($item->date_text),
                    'draw_date' => $item->draw_date?->format('Y-m-d'),
                ];
            })
            ->toArray();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'latestResult' => $latestResult,
            'availableDraws' => $availableDraws,
        ]);
    }

    /**
     * ตรวจหวย API
     */
    public function checkLottery(Request $request)
    {
        $lottoId = $request->input('lotto_id');
        $number = $request->input('number');

        if (!$lottoId || !$number) {
            return response()->json(['error' => 'กรุณากรอกข้อมูลให้ครบ'], 400);
        }

        // ดึงข้อมูลงวดที่เลือก
        $lotto = LottoData::where('lotto_id', $lottoId)->first();
        if (!$lotto) {
            return response()->json(['error' => 'ไม่พบข้อมูลงวดที่เลือก'], 404);
        }

        // ดึงรางวัลทั้งหมด
        $prizes = LottoPrize::where('lotto_id', $lottoId)->get();
        $runningNumbers = LottoRunningNumber::where('lotto_id', $lottoId)->get();

        $results = [];
        $totalWinnings = 0;

        // ตรวจรางวัลที่ 1 (6 หลัก)
        if (strlen($number) === 6) {
            $firstPrize = $prizes->where('prize_id', 'prizeFirst')->first();
            if ($firstPrize && in_array($number, $firstPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลที่ 1',
                    'reward' => $firstPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $firstPrize->reward));
            }

            // ตรวจรางวัลข้างเคียงรางวัลที่ 1
            $nearbyPrize = $prizes->where('prize_id', 'prizeFirstNear')->first();
            if ($nearbyPrize && in_array($number, $nearbyPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลข้างเคียงรางวัลที่ 1',
                    'reward' => $nearbyPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $nearbyPrize->reward));
            }

            // ตรวจรางวัลที่ 2
            $secondPrize = $prizes->where('prize_id', 'prizeSecond')->first();
            if ($secondPrize && in_array($number, $secondPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลที่ 2',
                    'reward' => $secondPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $secondPrize->reward));
            }

            // ตรวจรางวัลที่ 3
            $thirdPrize = $prizes->where('prize_id', 'prizeThird')->first();
            if ($thirdPrize && in_array($number, $thirdPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลที่ 3',
                    'reward' => $thirdPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $thirdPrize->reward));
            }

            // ตรวจรางวัลที่ 4
            $fourthPrize = $prizes->where('prize_id', 'prizeForth')->first();
            if ($fourthPrize && in_array($number, $fourthPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลที่ 4',
                    'reward' => $fourthPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $fourthPrize->reward));
            }

            // ตรวจรางวัลที่ 5
            $fifthPrize = $prizes->where('prize_id', 'prizeFifth')->first();
            if ($fifthPrize && in_array($number, $fifthPrize->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'รางวัลที่ 5',
                    'reward' => $fifthPrize->reward,
                    'matched' => true,
                ];
                $totalWinnings += intval(str_replace(',', '', $fifthPrize->reward));
            }
        }

        // ตรวจเลขท้าย 3 ตัว
        if (strlen($number) >= 3) {
            $lastThree = substr($number, -3);
            $backThree = $runningNumbers->where('running_id', 'runningNumberBackThree')->first();
            if ($backThree && in_array($lastThree, $backThree->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'เลขท้าย 3 ตัว',
                    'reward' => $backThree->reward,
                    'matched' => true,
                    'matched_number' => $lastThree,
                ];
                $totalWinnings += intval(str_replace(',', '', $backThree->reward));
            }
        }

        // ตรวจเลขหน้า 3 ตัว
        if (strlen($number) >= 3) {
            $frontThree = substr($number, 0, 3);
            $frontThreeData = $runningNumbers->where('running_id', 'runningNumberFrontThree')->first();
            if ($frontThreeData && in_array($frontThree, $frontThreeData->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'เลขหน้า 3 ตัว',
                    'reward' => $frontThreeData->reward,
                    'matched' => true,
                    'matched_number' => $frontThree,
                ];
                $totalWinnings += intval(str_replace(',', '', $frontThreeData->reward));
            }
        }

        // ตรวจเลขท้าย 2 ตัว
        if (strlen($number) >= 2) {
            $lastTwo = substr($number, -2);
            $backTwo = $runningNumbers->where('running_id', 'runningNumberBackTwo')->first();
            if ($backTwo && in_array($lastTwo, $backTwo->numbers ?? [])) {
                $results[] = [
                    'prize_name' => 'เลขท้าย 2 ตัว',
                    'reward' => $backTwo->reward,
                    'matched' => true,
                    'matched_number' => $lastTwo,
                ];
                $totalWinnings += intval(str_replace(',', '', $backTwo->reward));
            }
        }

        return response()->json([
            'success' => true,
            'draw_date' => $this->formatDateText($lotto->date_text),
            'number' => $number,
            'results' => $results,
            'total_winnings' => $totalWinnings,
            'is_winner' => count($results) > 0,
        ]);
    }

    /**
     * Format date text
     */
    private function formatDateText(string $dateText): string
    {
        $dateText = str_replace('ลากกินแบ่งรัฐบาลงวดประจำวันที่-', '', $dateText);
        $dateText = str_replace('กกินแบ่งรัฐบาล งวดประจำวันที่', '', $dateText);
        $dateText = str_replace('กกินแบ่งรัฐบาล', '', $dateText);
        $dateText = str_replace('ลา ', '', $dateText);
        $dateText = str_replace('ผสลา', '', $dateText);
        $dateText = str_replace('งวด', '', $dateText);
        $dateText = str_replace('ผสล', '', $dateText);
        $dateText = str_replace('ประจำวันที่', '', $dateText);
        $dateText = str_replace('-', ' ', $dateText);
        return trim($dateText);
    }
}
