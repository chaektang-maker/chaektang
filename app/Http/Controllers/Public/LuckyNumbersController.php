<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\LottoData;
use App\Models\Source;
use App\Models\UserVote;
use App\Services\SourceTrackRecordService;
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

        $canViewVip = ($request->user('customer') && $request->user('customer')->isVip())
            || ($request->user('web') && $request->user('web')->isAdmin());

        $filterSourceId = $request->integer('source_id') ?: null;
        $selectedSource = $filterSourceId
            ? Source::where('status', 'active')->find($filterSourceId, ['id', 'name'])
            : null;
        if (!$selectedSource) {
            $filterSourceId = null;
        }

        $query = LotteryNumber::query()
            ->with(['source'])
            ->join('sources', 'sources.id', '=', 'lottery_numbers.source_id')
            ->where('lottery_numbers.draw_date', $drawDate)
            ->where('sources.status', 'active')
            ->whereNull('sources.deleted_at');

        if ($filterSourceId) {
            $query->where('lottery_numbers.source_id', $filterSourceId);
        }

        // สำนักที่กำลังโปรโมท (is_promoted = true และยังไม่หมดอายุ promoted_until) แสดงขึ้นก่อนเสมอ
        $query->orderByRaw('CASE WHEN sources.is_promoted = 1 AND (sources.promoted_until IS NULL OR DATE(sources.promoted_until) >= CURDATE()) THEN 1 ELSE 0 END DESC');

        if ($sort === 'popular') {
            $query->orderByDesc('sources.popularity_score')
                ->orderByDesc('lottery_numbers.updated_at');
        } else {
            // latest
            $query->orderByDesc('lottery_numbers.updated_at')
                ->orderByDesc('sources.popularity_score');
        }

        $query->select('lottery_numbers.*');

        $numbers = $query->paginate(24)->withQueryString();

        // ดึงรายการสำนักแม่นยำแนะนำ (3 งวดติด) + สำนักคะแนนความนิยมสูงสุดในงวดนี้ เพื่อกำหนดเป็นสำนัก VIP
        $trackRanking = app(SourceTrackRecordService::class)->getTrackRecordRanking(10);
        $vipSourceIds = collect($trackRanking['recommended_source_ids'] ?? []);

        // หากในงวดนี้มีมากกว่า 1 สำนัก ให้สำนักที่ได้ความนิยมสูงสุดอันดับ 1 ของงวด (ที่ไม่ได้กำลังโปรโมทสปอนเซอร์) เป็น VIP ด้วยเสมอ
        $topPopularSourceIds = LotteryNumber::query()
            ->join('sources', 'sources.id', '=', 'lottery_numbers.source_id')
            ->where('lottery_numbers.draw_date', $drawDate)
            ->where('sources.status', 'active')
            ->whereNull('sources.deleted_at')
            ->whereRaw('NOT (sources.is_promoted = 1 AND (sources.promoted_until IS NULL OR DATE(sources.promoted_until) >= CURDATE()))')
            ->orderByDesc('sources.popularity_score')
            ->pluck('sources.id')
            ->unique()
            ->values();

        if ($topPopularSourceIds->count() > 1) {
            $vipSourceIds->push($topPopularSourceIds->first());
        }
        $vipSourceIds = $vipSourceIds->unique()->values()->all();

        // ดึง source_ids ทั้งหมดในงวดนี้
        $sourceIds = $numbers->pluck('source_id')->unique()->toArray();

        // นับยอดการมองเห็น (Impression) ของสำนักที่แสดงในหน้านี้ (1 ครั้งต่อ Session ต่องวด)
        $sessionKey = 'viewed_lucky_sources_' . $drawDate;
        $viewedSources = $request->session()->get($sessionKey, []);
        $newViewedIds = array_values(array_diff($sourceIds, $viewedSources));
        if (!empty($newViewedIds)) {
            Source::whereIn('id', $newViewedIds)->increment('view_count');
            $request->session()->put($sessionKey, array_values(array_unique(array_merge($viewedSources, $newViewedIds))));
        }

        // ดึงจำนวนโหวตสำหรับแต่ละสำนัก
        $voteCounts = UserVote::whereIn('source_id', $sourceIds)
            ->where('draw_date', $drawDate)
            ->selectRaw('source_id, COUNT(*) as count')
            ->groupBy('source_id')
            ->pluck('count', 'source_id')
            ->toArray();

        // คำนวณสิทธิ์การโหวตของผู้ใช้ปัจจุบันในงวดนี้ (ส่งไปรอบเดียว ไม่ต้องให้การ์ด 24 ใบยิง API ซ้ำ)
        $customerId = Auth::guard('customer')->id();
        $userId = $customerId ? null : Auth::guard('web')->id();
        $isVoterLoggedIn = (bool) ($customerId || $userId);
        $remainingVotes = 0;

        if ($isVoterLoggedIn) {
            $voterColumn = $customerId ? 'customer_id' : 'user_id';
            $voterId = $customerId ?: $userId;
            $usedVotes = UserVote::where($voterColumn, $voterId)
                ->where('draw_date', $drawDate)
                ->count();
            $remainingVotes = max(0, 3 - $usedVotes);
        }

        // เพิ่มข้อมูล vote count, สถานะโปรโมท และซ่อนเลข 2 ตัว / 3 ตัวในฝั่ง Backend สำหรับผู้ที่ไม่ได้เป็น VIP
        // (ยกเว้นสำนักที่กำลังโปรโมทสปอนเซอร์ จะเปิดเลขให้เห็นครบ 100% เพื่อความคุ้มค่าของสปอนเซอร์)
        $numbers->getCollection()->transform(function ($number) use ($voteCounts, $vipSourceIds, $canViewVip, $newViewedIds) {
            $number->vote_count = $voteCounts[$number->source_id] ?? 0;
            if ($number->source && in_array($number->source_id, $newViewedIds, true)) {
                $number->source->view_count = (int) ($number->source->view_count ?? 0) + 1;
            }
            $number->is_currently_promoted = $number->source ? $number->source->isCurrentlyPromoted() : false;
            $number->is_vip_source = !$number->is_currently_promoted && in_array($number->source_id, $vipSourceIds, true);
            $number->is_vip_locked = $number->is_vip_source && !$canViewVip;

            if ($number->is_vip_locked) {
                $number->two_digit = '••';
                $number->three_digit = '•••';
            }

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
            ->values()
            ->map(function ($item, $index) use ($canViewVip) {
                $isLocked = ($index === 0) && !$canViewVip;
                return [
                    'rank' => $index + 1,
                    'number' => $isLocked ? '••' : $item->two_digit,
                    'count' => $item->count,
                    'is_vip_locked' => $isLocked,
                ];
            })
            ->toArray();

        // ส่งเป็น array ของ { value, label } ให้ front เลือกงวดได้
        return Inertia::render('LuckyNumbers/Index', [
            'filters' => [
                'draw_date' => $drawDate,
                'sort' => $sort,
                'source_id' => $filterSourceId,
            ],
            'selectedSource' => $selectedSource ? [
                'id' => $selectedSource->id,
                'name' => $selectedSource->name,
            ] : null,
            'availableDrawDates' => $availableDrawDates,
            'numbers' => $numbers,
            'hotNumbers' => $hotNumbers,
            'canViewVip' => $canViewVip,
            'voteStatus' => [
                'is_logged_in' => $isVoterLoggedIn,
                'remaining_votes' => $remainingVotes,
            ],
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
