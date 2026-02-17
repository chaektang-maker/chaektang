<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\LottoData;
use App\Models\Source;
use App\Models\UserVote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LuckyNumbersController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->string('sort')->toString() ?: 'latest';

        // ดึงรายการงวดจาก lotto_data เท่านั้น (ไม่สน is_fetched)
        $availableDrawDates = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->get()
            ->map(function ($item) {
                $dateText = str_replace('ลากกินแบ่งรัฐบาลงวดประจำวันที่-', '', $item->date_text ?? '');
                $dateText = str_replace('กกินแบ่งรัฐบาล งวดประจำวันที่', '', $dateText);
                $dateText = str_replace('กกินแบ่งรัฐบาล', '', $dateText);
                $dateText = str_replace('ลา ', '', $dateText);
                $dateText = str_replace('ผสลา', '', $dateText);
                $dateText = str_replace('งวด', '', $dateText);
                $dateText = str_replace('ผสล', '', $dateText);
                $dateText = str_replace('ประจำวันที่', '', $dateText);
                $dateText = str_replace('-', ' ', $dateText);
                $dateText = trim($dateText);
                $drawDateStr = $item->draw_date ? $item->draw_date->format('Y-m-d') : null;
                return ['value' => $drawDateStr, 'label' => $dateText ?: $drawDateStr];
            })
            ->filter(fn ($item) => !empty($item['value']))
            ->values()
            ->all();

        $drawDate = $request->string('draw_date')->toString();

        // ครั้งแรกหรือไม่ส่งงวดมา = ใช้งวดล่าสุดจาก lotto_data
        if (!$drawDate && count($availableDrawDates) > 0) {
            $drawDate = $availableDrawDates[0]['value'];
        }
        if (!$drawDate) {
            $drawDate = now()->toDateString();
        }

        // ถ้าส่งงวดมา ต้องมีใน lotto_data เท่านั้น (ป้องกันวันที่มั่ว)
        $validDates = collect($availableDrawDates)->pluck('value')->toArray();
        if ($drawDate && !in_array($drawDate, $validDates, true)) {
            $drawDate = count($availableDrawDates) > 0 ? $availableDrawDates[0]['value'] : now()->toDateString();
        }

        $query = LotteryNumber::query()
            ->with(['source'])
            ->where('draw_date', $drawDate)
            ->whereHas('source', function (Builder $q) {
                $q->where('status', 'active');
            });

        if ($sort === 'popular') {
            $query->join('sources', 'sources.id', '=', 'lottery_numbers.source_id')
                ->orderByDesc('sources.popularity_score')
                ->select('lottery_numbers.*');
        } else {
            // latest
            $query->orderByDesc('updated_at');
        }

        $numbers = $query->paginate(24)->withQueryString();

        // ดึง source_ids ทั้งหมดในงวดนี้
        $sourceIds = $numbers->pluck('source_id')->unique()->toArray();

        // ดึงจำนวนโหวตสำหรับแต่ละสำนัก
        $voteCounts = UserVote::whereIn('source_id', $sourceIds)
            ->where('draw_date', $drawDate)
            ->selectRaw('source_id, COUNT(*) as count')
            ->groupBy('source_id')
            ->pluck('count', 'source_id')
            ->toArray();

        // เพิ่มข้อมูล vote count ให้กับแต่ละ number
        $numbers->getCollection()->transform(function ($number) use ($voteCounts) {
            $number->vote_count = $voteCounts[$number->source_id] ?? 0;
            return $number;
        });

        // คำนวณเลขเด็ดมาแรง 2 ตัว (เลขที่ออกบ่อยที่สุดจากทุกสำนัก)
        $hotNumbers = LotteryNumber::query()
            ->where('draw_date', $drawDate)
            ->whereNotNull('two_digit')
            ->where('two_digit', '!=', '')
            ->whereHas('source', function (Builder $q) {
                $q->where('status', 'active');
            })
            ->selectRaw('two_digit, COUNT(*) as count')
            ->groupBy('two_digit')
            ->orderByDesc('count')
            ->orderBy('two_digit')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'number' => $item->two_digit,
                    'count' => $item->count,
                ];
            })
            ->toArray();

        // ส่งเป็น array ของ { value, label } ให้ front เลือกงวดได้
        return Inertia::render('LuckyNumbers/Index', [
            'filters' => [
                'draw_date' => $drawDate,
                'sort' => $sort,
            ],
            'availableDrawDates' => $availableDrawDates,
            'numbers' => $numbers,
            'hotNumbers' => $hotNumbers,
        ]);
    }

    /**
     * แปลง date_text เป็น draw_date (YYYY-MM-DD)
     * 
     * @param string $dateText เช่น "ลากกินแบ่งรัฐบาลงวดประจำวันที่-16-กรกฎาคม-2550"
     * @return string|null เช่น "2007-07-16"
     */
    private function parseDateTextToDrawDate(string $dateText): ?string
    {
        try {
            // ตัดคำว่า "ลากกินแบ่งรัฐบาลงวดประจำวันที่-" ออก
            $date = str_replace('ลากกินแบ่งรัฐบาลงวดประจำวันที่-', '', $dateText);
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
                return null;
            }

            // แยกวันที่ เดือน ปี
            $parts = array_filter(explode(' ', trim($date)));
            $parts = array_values($parts);
            
            if (count($parts) >= 3) {
                $day = (int)$parts[0];
                $month = (int)$parts[1];
                $year = (int)$parts[2];

                // แปลงปี พ.ศ. เป็น ค.ศ.
                $year = $year - 543;

                // สร้าง date string
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
