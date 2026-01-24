<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\LottoData;
use App\Models\Source;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LuckyNumbersController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->string('sort')->toString() ?: 'latest';
        $drawDate = $request->string('draw_date')->toString();

        if (!$drawDate) {
            $drawDate = LotteryNumber::query()->max('draw_date') ?: now()->toDateString();
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

        // ดึงข้อมูลงวดจาก lotto_data และแปลง date_text
        // เรียงตาม draw_date จากมากไปน้อย (งวดล่าสุดก่อน)
        $availableDrawDates = LottoData::query()
            ->where('is_fetched', 1)
            ->whereNotNull('draw_date') // กรองเฉพาะที่มี draw_date
            ->select('date_text', 'lotto_id', 'draw_date')
            ->orderByDesc('draw_date') // เรียงตาม draw_date จากมากไปน้อย (งวดล่าสุดก่อน)
            ->get()
            ->map(function ($item) {
                // ตัดคำว่า "ลากกินแบ่งรัฐบาลงวดประจำวันที่-" ออก
                $dateText = str_replace('ลากกินแบ่งรัฐบาลงวดประจำวันที่-', '', $item->date_text);
                $dateText = str_replace('กกินแบ่งรัฐบาล งวดประจำวันที่', '', $dateText);
                $dateText = str_replace('กกินแบ่งรัฐบาล', '', $dateText);
                $dateText = str_replace('ลา ', '', $dateText);
                $dateText = str_replace('ผสลา', '', $dateText);
                $dateText = str_replace('งวด', '', $dateText);
                $dateText = str_replace('ผสล', '', $dateText);
                $dateText = str_replace('ประจำวันที่', '', $dateText);

                // ตัดเครื่องหมาย "-" ออก
                $dateText = str_replace('-', ' ', $dateText);
                // trim whitespace
                $dateText = trim($dateText);
                
                // ใช้ draw_date จาก database โดยตรง
                $drawDate = $item->draw_date ? $item->draw_date->format('Y-m-d') : null;
                
                return [
                    'value' => $drawDate, // ใช้ draw_date เป็น value
                    'label' => $dateText, // แสดงวันที่ที่แปลงแล้ว (จาก date_text)
                ];
            })
            ->filter(function ($item) {
                return !empty($item['value']); // กรองเฉพาะที่มี draw_date
            })
            ->pluck('label', 'value') // pluck หลังจาก sort แล้ว
            ->toArray();

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
