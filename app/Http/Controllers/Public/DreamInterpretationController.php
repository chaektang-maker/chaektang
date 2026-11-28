<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\DreamInterpretation;
use App\Models\LotteryNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * หน้าตำราทำนายฝัน & ถอดรหัสเลขมงคล (แนวทาง A: ค้นหาฟรีไม่จำกัด + ล็อกเลข 3 ตัว & เลขฝันชนสำนักดังให้สมาชิก VIP)
 */
class DreamInterpretationController extends Controller
{
    public function index(Request $request)
    {
        DreamInterpretation::ensureTableExists();

        $customer = $request->user('customer');
        $admin = $request->user('web');
        $canViewVip = ($customer && $customer->isVip()) || ($admin && $admin->isAdmin());

        $query = trim((string) $request->query('q', $request->old('dream', '')));
        $letter = trim((string) $request->query('letter', ''));

        $allItems = DreamInterpretation::query()
            ->orderBy('keyword')
            ->get();

        $availableLetters = $allItems
            ->pluck('category_letter')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // ดึงเลขเด็ดสำนักดังในงวดล่าสุด เพื่อนำมาจับคู่ว่า "เลขจากความฝัน ชนกับสำนักไหนบ้าง"
        $latestDrawDate = LotteryNumber::query()->max('draw_date');
        $currentDrawNumbers = collect();
        if ($latestDrawDate) {
            $currentDrawNumbers = LotteryNumber::query()
                ->with('source:id,name,status')
                ->where('draw_date', $latestDrawDate)
                ->whereHas('source', fn ($q) => $q->where('status', 'active'))
                ->get(['id', 'source_id', 'draw_date', 'two_digit', 'three_digit']);
        }

        // กรองรายการตามคำค้นหา (รองรับทั้งพิมพ์ประโยคความฝัน เช่น "เมื่อคืนฝันเห็นกุญแจกับกระต่ายน้อย" หรือพิมพ์คำสั้นๆ)
        $matchedItems = $allItems;

        if ($query !== '') {
            $qLower = mb_strtolower($query);

            // 1. เช็กว่าในประโยคความฝันที่ผู้ใช้พิมพ์ มี keyword ในตำราคำไหนปรากฏอยู่บ้าง หรือ keyword มีคำที่พิมพ์
            $keywordMatches = $allItems->filter(function (DreamInterpretation $item) use ($qLower) {
                $kw = mb_strtolower(trim($item->keyword));
                if ($kw === '') {
                    return false;
                }
                return mb_strpos($qLower, $kw) !== false || mb_strpos($kw, $qLower) !== false;
            });

            if ($keywordMatches->isNotEmpty()) {
                $matchedItems = $keywordMatches->values();
            } else {
                // 2. ถ้าไม่ตรงกับชื่อหัวข้อโดยตรง ให้ค้นหาในเนื้อหาคำทำนายหรือตัวเลขด้วย
                $matchedItems = $allItems->filter(function (DreamInterpretation $item) use ($qLower) {
                    return mb_strpos(mb_strtolower($item->meaning), $qLower) !== false
                        || mb_strpos((string) $item->two_digits, $qLower) !== false
                        || mb_strpos((string) $item->three_digits, $qLower) !== false;
                })->values();
            }

            // นับจำนวนการค้นหาให้กับคำที่ตรง
            foreach ($matchedItems->take(5) as $matched) {
                DreamInterpretation::where('id', $matched->id)->increment('search_count');
            }
        } elseif ($letter !== '' && $letter !== 'all') {
            $matchedItems = $allItems->where('category_letter', $letter)->values();
        }

        // แปลงข้อมูลส่งให้หน้าบ้านตามสิทธิ์ (แนวทาง A: ฟรีคำทำนาย + เลข 2 ตัว / ล็อกเลข 3 ตัว + รายชื่อสำนักที่ชนให้ VIP)
        // เพิ่มระบบแบ่งหน้า (Pagination หน้าละ 20 รายการ) เพื่อรองรับข้อมูลหลักพันคำโดยไม่ทำให้หน้าเว็บช้า
        $perPage = 20;
        $totalMatched = $matchedItems->count();
        $totalPages = max(1, (int) ceil($totalMatched / $perPage));
        $currentPage = min($totalPages, max(1, $request->integer('page', 1)));

        $pagedItems = $matchedItems->forPage($currentPage, $perPage)->values();

        $formattedItems = $pagedItems->map(function (DreamInterpretation $item) use ($canViewVip, $currentDrawNumbers) {
            $twoDigitList = $item->two_digit_array;
            $threeDigitList = $item->three_digit_array;
            $allDreamNums = array_merge($twoDigitList, $threeDigitList);

            $matchingSources = [];
            if (!empty($allDreamNums) && $currentDrawNumbers->isNotEmpty()) {
                foreach ($currentDrawNumbers as $lottoNum) {
                    $two = trim((string) $lottoNum->two_digit);
                    $three = trim((string) $lottoNum->three_digit);

                    $matchedDigits = [];
                    if ($two !== '' && in_array($two, $twoDigitList, true)) {
                        $matchedDigits[] = $two;
                    }
                    if ($three !== '' && in_array($three, $threeDigitList, true)) {
                        $matchedDigits[] = $three;
                    }

                    if (!empty($matchedDigits) && $lottoNum->source) {
                        $matchingSources[] = [
                            'source_name' => $lottoNum->source->name,
                            'matched_numbers' => implode(', ', $matchedDigits),
                        ];
                    }
                }
            }

            return [
                'id' => $item->id,
                'keyword' => $item->keyword,
                'category_letter' => $item->category_letter,
                'meaning' => $item->meaning,
                'two_digit_list' => $twoDigitList,
                'three_digit_count' => count($threeDigitList),
                'three_digit_list' => $canViewVip ? $threeDigitList : [],
                'no_number_note' => $item->no_number_note,
                'matching_sources_count' => count($matchingSources),
                'matching_sources' => $canViewVip ? $matchingSources : [],
            ];
        })->values();

        // คำฝันยอดฮิต / แนะนำสำหรับคลิกด่วน
        $popularKeywords = $allItems
            ->sortByDesc('search_count')
            ->take(12)
            ->pluck('keyword')
            ->values();

        $affiliateProducts = AffiliateProduct::query()
            ->with('platform:id,name,logo_url')
            ->where('is_active', true)
            ->whereHas('platform', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->limit(4)
            ->get(['id', 'platform_id', 'title', 'description', 'image_url', 'affiliate_url']);

        return Inertia::render('DreamInterpretation/Index', [
            'searchQuery' => $query,
            'selectedLetter' => $letter ?: 'all',
            'availableLetters' => $availableLetters,
            'popularKeywords' => $popularKeywords,
            'dreamItems' => $formattedItems,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $totalPages,
                'per_page' => $perPage,
                'total' => $totalMatched,
            ],
            'totalDictionaryCount' => $allItems->count(),
            'canViewVip' => $canViewVip,
            'affiliateProducts' => $affiliateProducts,
        ]);
    }

    /**
     * รองรับการกด Submit ฟอร์มค้นหาความฝันแบบ POST แล้วเปลี่ยนไปหน้าผลลัพธ์พร้อมพารามิเตอร์ q
     */
    public function interpret(Request $request)
    {
        $q = trim((string) $request->input('dream', $request->input('q', '')));

        return redirect()->route('dream-interpretation.index', $q !== '' ? ['q' => $q] : []);
    }
}
