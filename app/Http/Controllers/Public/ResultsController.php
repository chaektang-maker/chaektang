<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LottoData;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        $lottoId = $request->string('lotto_id')->toString();

        // ดึงรายการงวดทั้งหมด
        $availableDraws = LottoData::query()
            ->where('is_fetched', 1)
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->lotto_id,
                    'label' => $this->formatDateText($item->date_text),
                    'draw_date' => $item->draw_date?->format('Y-m-d'),
                ];
            })
            ->toArray();

        // ถ้าไม่ได้เลือกงวด ให้ใช้งวดล่าสุด
        if (!$lottoId && count($availableDraws) > 0) {
            $lottoId = $availableDraws[0]['value'];
        }

        // ดึงข้อมูลผลรางวัลของงวดที่เลือก
        $result = null;
        if ($lottoId) {
            $lotto = LottoData::where('lotto_id', $lottoId)->first();
            if ($lotto) {
                $prizes = LottoPrize::where('lotto_id', $lottoId)->get();
                $runningNumbers = LottoRunningNumber::where('lotto_id', $lottoId)->get();

                $result = [
                    'lotto_id' => $lotto->lotto_id,
                    'draw_date' => $lotto->draw_date?->format('Y-m-d'),
                    'date_text' => $this->formatDateText($lotto->date_text),
                    'prizes' => $this->formatPrizes($prizes),
                    'running_numbers' => $this->formatRunningNumbers($runningNumbers),
                ];
            }
        }

        return Inertia::render('Results/Index', [
            'availableDraws' => $availableDraws,
            'selectedLottoId' => $lottoId,
            'result' => $result,
        ]);
    }

    /**
     * Format prizes
     */
    private function formatPrizes($prizes)
    {
        $formatted = [];

        // รางวัลที่ 1
        $firstPrize = $prizes->where('prize_id', 'prizeFirst')->first();
        if ($firstPrize) {
            $formatted['first'] = [
                'name' => 'รางวัลที่ 1',
                'reward' => $firstPrize->reward,
                'numbers' => $firstPrize->numbers ?? [],
            ];
        }

        // รางวัลข้างเคียงรางวัลที่ 1
        $nearbyPrize = $prizes->where('prize_id', 'prizeFirstNear')->first();
        if ($nearbyPrize) {
            $formatted['nearby'] = [
                'name' => 'รางวัลข้างเคียงรางวัลที่ 1',
                'reward' => $nearbyPrize->reward,
                'numbers' => $nearbyPrize->numbers ?? [],
            ];
        }

        // รางวัลที่ 2
        $secondPrize = $prizes->where('prize_id', 'prizeSecond')->first();
        if ($secondPrize) {
            $formatted['second'] = [
                'name' => 'รางวัลที่ 2',
                'reward' => $secondPrize->reward,
                'numbers' => $secondPrize->numbers ?? [],
            ];
        }

        // รางวัลที่ 3
        $thirdPrize = $prizes->where('prize_id', 'prizeThird')->first();
        if ($thirdPrize) {
            $formatted['third'] = [
                'name' => 'รางวัลที่ 3',
                'reward' => $thirdPrize->reward,
                'numbers' => $thirdPrize->numbers ?? [],
            ];
        }

        // รางวัลที่ 4
        $fourthPrize = $prizes->where('prize_id', 'prizeForth')->first();
        if ($fourthPrize) {
            $formatted['fourth'] = [
                'name' => 'รางวัลที่ 4',
                'reward' => $fourthPrize->reward,
                'numbers' => $fourthPrize->numbers ?? [],
            ];
        }

        // รางวัลที่ 5
        $fifthPrize = $prizes->where('prize_id', 'prizeFifth')->first();
        if ($fifthPrize) {
            $formatted['fifth'] = [
                'name' => 'รางวัลที่ 5',
                'reward' => $fifthPrize->reward,
                'numbers' => $fifthPrize->numbers ?? [],
            ];
        }

        return $formatted;
    }

    /**
     * Format running numbers
     */
    private function formatRunningNumbers($runningNumbers)
    {
        $formatted = [];

        // เลขหน้า 3 ตัว
        $frontThree = $runningNumbers->where('running_id', 'runningNumberFrontThree')->first();
        if ($frontThree) {
            $formatted['front_three'] = [
                'name' => 'เลขหน้า 3 ตัว',
                'reward' => $frontThree->reward,
                'numbers' => $frontThree->numbers ?? [],
            ];
        }

        // เลขท้าย 3 ตัว
        $backThree = $runningNumbers->where('running_id', 'runningNumberBackThree')->first();
        if ($backThree) {
            $formatted['back_three'] = [
                'name' => 'เลขท้าย 3 ตัว',
                'reward' => $backThree->reward,
                'numbers' => $backThree->numbers ?? [],
            ];
        }

        // เลขท้าย 2 ตัว
        $backTwo = $runningNumbers->where('running_id', 'runningNumberBackTwo')->first();
        if ($backTwo) {
            $formatted['back_two'] = [
                'name' => 'เลขท้าย 2 ตัว',
                'reward' => $backTwo->reward,
                'numbers' => $backTwo->numbers ?? [],
            ];
        }

        return $formatted;
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
