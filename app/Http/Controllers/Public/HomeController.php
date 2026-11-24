<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LotteryNumber;
use App\Models\LottoData;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use App\Models\Platform;
use App\Models\Post;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // ดึงงวดล่าสุด (ไม่จำเป็นต้อง fetched แล้ว)
        $latestLotto = LottoData::query()
            ->whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->first();

        $latestResult = null;
        if ($latestLotto) {
            if (!$latestLotto->is_fetched) {
                // ถ้างวดนี้ยังไม่ดึงผล ให้โชว์เลขเป็น x แทน
                $latestResult = [
                    'lotto_id' => $latestLotto->lotto_id,
                    'draw_date' => $latestLotto->draw_date?->format('Y-m-d'),
                    'date_text' => $this->formatDateText($latestLotto->date_text),
                    'first_prize' => 'xxxxxx',
                    'last_two_digit' => 'xx',
                    'front_three_digit' => ['xxx', 'xxx'],
                    'last_three_digit' => ['xxx', 'xxx'],
                    'nearby_prizes' => [],
                    'second_prizes' => [],
                ];
            } else {
                // ดึงผลจริงของงวดล่าสุดที่ดึงแล้ว
                $firstPrize = LottoPrize::where('lotto_id', $latestLotto->lotto_id)
                    ->where('prize_id', 'prizeFirst')
                    ->first();

                $lastTwoDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                    ->where('running_id', 'runningNumberBackTwo')
                    ->first();

                $frontThreeDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                    ->where('running_id', 'runningNumberFrontThree')
                    ->first();

                $lastThreeDigit = LottoRunningNumber::where('lotto_id', $latestLotto->lotto_id)
                    ->where('running_id', 'runningNumberBackThree')
                    ->first();

                $nearbyPrize = LottoPrize::where('lotto_id', $latestLotto->lotto_id)
                    ->where('prize_id', 'prizeFirstNear')
                    ->first();

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
        }

        // ดึงรายการงวดทั้งหมดสำหรับ dropdown ตรวจสลาก (ใช้ทุกงวดที่มี draw_date ไม่เฉพาะ is_fetched)
        $availableDraws = LottoData::query()
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

        // แพลตฟอร์มที่มีสินค้า affiliate เปิดแสดง — แยก section ตามแพลตฟอร์ม
        $affiliateSections = Platform::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['affiliateProducts' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->filter(fn ($p) => $p->affiliateProducts->isNotEmpty())
            ->map(fn ($p) => [
                'platform' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'logo_url' => $p->logo_url,
                ],
                'products' => $p->affiliateProducts->map(fn ($prod) => [
                    'id' => $prod->id,
                    'title' => $prod->title,
                    'description' => $prod->description,
                    'image_url' => $prod->image_url,
                    'affiliate_url' => $prod->affiliate_url,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        // ดึงเลขเด็ดสำนักแนะนำ (Sponsored Sources) ของงวดล่าสุดที่มีการลงเลข
        $latestNumberDrawDate = LotteryNumber::query()
            ->whereHas('source', function ($q) {
                $q->where('status', 'active')
                    ->where('is_promoted', true)
                    ->where(function ($sub) {
                        $sub->whereNull('promoted_until')
                            ->orWhereDate('promoted_until', '>=', now()->toDateString());
                    });
            })
            ->max('draw_date');

        $promotedNumbers = [];
        if ($latestNumberDrawDate) {
            $promotedCollection = LotteryNumber::query()
                ->with('source')
                ->join('sources', 'sources.id', '=', 'lottery_numbers.source_id')
                ->where('lottery_numbers.draw_date', $latestNumberDrawDate)
                ->where('sources.status', 'active')
                ->whereNull('sources.deleted_at')
                ->where('sources.is_promoted', true)
                ->where(function ($q) {
                    $q->whereNull('sources.promoted_until')
                        ->orWhereDate('sources.promoted_until', '>=', now()->toDateString());
                })
                ->orderByDesc('sources.popularity_score')
                ->orderByDesc('lottery_numbers.updated_at')
                ->select('lottery_numbers.*')
                ->limit(6)
                ->get();

            // นับยอดการมองเห็น (Impression) สำหรับสำนักสปอนเซอร์บนหน้าแรก (1 ครั้งต่อ Session)
            $promotedSourceIds = $promotedCollection->pluck('source_id')->unique()->values()->all();
            $viewedHomeSources = $request->session()->get('viewed_home_sources', []);
            $newViewedIds = array_values(array_diff($promotedSourceIds, $viewedHomeSources));
            if (!empty($newViewedIds)) {
                Source::whereIn('id', $newViewedIds)->increment('view_count');
                $request->session()->put('viewed_home_sources', array_values(array_unique(array_merge($viewedHomeSources, $newViewedIds))));
            }

            $promotedNumbers = $promotedCollection
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'draw_date' => $n->draw_date instanceof \DateTimeInterface
                        ? $n->draw_date->format('Y-m-d')
                        : (string) $n->draw_date,
                    'two_digit' => $n->two_digit,
                    'three_digit' => $n->three_digit,
                    'running_numbers' => $n->running_numbers,
                    'source' => [
                        'id' => $n->source?->id,
                        'name' => $n->source?->name,
                        'description' => $n->source?->description,
                        'view_count' => (int) ($n->source?->view_count ?? 0) + (in_array($n->source_id, $newViewedIds, true) ? 1 : 0),
                    ],
                ])
                ->values()
                ->all();
        }

        // ดึงบทความล่าสุด 3 บทความเพื่อแสดงบนหน้าแรก (ช่วยดัน SEO และยอดคนอ่าน)
        $latestPosts = Post::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Post $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'excerpt' => $p->excerpt,
                'cover_image_url' => \App\Services\MediaStorageService::url($p->cover_image),
                'author_name' => $p->author?->name ?? 'ทีมงานแจกตัง',
                'published_at' => ($p->published_at ?? $p->created_at)?->format('d/m/Y'),
                'view_count' => (int) ($p->view_count ?? 0),
            ])
            ->values()
            ->all();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'latestResult' => $latestResult,
            'availableDraws' => $availableDraws,
            'affiliateSections' => $affiliateSections,
            'promotedNumbers' => $promotedNumbers,
            'latestPosts' => $latestPosts,
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
