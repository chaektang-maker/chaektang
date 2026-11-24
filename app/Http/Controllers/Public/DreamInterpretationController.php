<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Services\DreamInterpretationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

/**
 * หน้าและ API ทำนายฝัน (ใช้ Google AI Studio / Gemini)
 */
class DreamInterpretationController extends Controller
{
    /**
     * คำนวณโควตาทำนายฝันรายวันตามระดับผู้ใช้งาน
     * - Guest (ไม่ล็อกอิน): 1 ครั้ง/วัน
     * - สมาชิกฟรี (Customer): 3 ครั้ง/วัน
     * - สมาชิก VIP / Admin: ไม่จำกัด
     */
    private function getQuotaStatus(Request $request): array
    {
        $customer = $request->user('customer');
        $admin = $request->user('web');
        $today = now()->toDateString();

        if (($customer && $customer->isVip()) || ($admin && $admin->isAdmin())) {
            return [
                'tier' => 'vip',
                'is_unlimited' => true,
                'limit' => null,
                'used' => 0,
                'remaining' => null,
                'cache_key' => null,
            ];
        }

        if ($customer) {
            $limit = 3;
            $cacheKey = "dream_quota:customer:{$customer->id}:{$today}";
            $used = (int) Cache::get($cacheKey, 0);

            return [
                'tier' => 'member',
                'is_unlimited' => false,
                'limit' => $limit,
                'used' => $used,
                'remaining' => max(0, $limit - $used),
                'cache_key' => $cacheKey,
            ];
        }

        $limit = 1;
        $ip = $request->ip() ?: 'unknown';
        $cacheKey = "dream_quota:ip:{$ip}:{$today}";
        $used = (int) Cache::get($cacheKey, 0);

        return [
            'tier' => 'guest',
            'is_unlimited' => false,
            'limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'cache_key' => $cacheKey,
        ];
    }

    public function index(Request $request)
    {
        $quota = $this->getQuotaStatus($request);

        $affiliateProducts = AffiliateProduct::query()
            ->with('platform:id,name,logo_url')
            ->where('is_active', true)
            ->whereHas('platform', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->limit(4)
            ->get(['id', 'platform_id', 'title', 'description', 'image_url', 'affiliate_url']);

        return Inertia::render('DreamInterpretation/Index', [
            'oldDream' => $request->old('dream'),
            'quota' => [
                'tier' => $quota['tier'],
                'is_unlimited' => $quota['is_unlimited'],
                'limit' => $quota['limit'],
                'used' => $quota['used'],
                'remaining' => $quota['remaining'],
            ],
            'affiliateProducts' => $affiliateProducts,
        ]);
    }

    /**
     * รับข้อความฝันจากฟอร์ม → ตรวจสอบโควตา → เรียก AI → redirect กลับพร้อมผลทำนายใน session
     */
    public function interpret(Request $request)
    {
        $request->validate([
            'dream' => 'required|string|max:2000',
        ], [
            'dream.required' => 'กรุณาพิมพ์ความฝันของคุณ',
            'dream.max' => 'ความฝันยาวเกินไป (สูงสุด 2000 ตัวอักษร)',
        ]);

        $quota = $this->getQuotaStatus($request);

        if (!$quota['is_unlimited'] && $quota['remaining'] <= 0) {
            $msg = $quota['tier'] === 'guest'
                ? 'คุณใช้สิทธิ์ทำนายฝันฟรีครบ 1 ครั้ง/วันแล้ว กรุณาสมัครสมาชิกฟรีเพื่อรับสิทธิ์ 3 ครั้ง/วัน หรือสมัคร VIP เพื่อใช้งานไม่จำกัด'
                : 'คุณใช้โควตาทำนายฝันครบ 3 ครั้งสำหรับวันนี้แล้ว อัปเกรดเป็นสมาชิก VIP เพื่อทำนายฝันได้ไม่จำกัด!';

            return redirect()
                ->route('dream-interpretation.index')
                ->withInput($request->only('dream'))
                ->with('dream_error', $msg);
        }

        $service = app(DreamInterpretationService::class);
        $result = $service->interpret($request->input('dream'));

        if ($result['success']) {
            if (!$quota['is_unlimited'] && $quota['cache_key']) {
                Cache::put($quota['cache_key'], $quota['used'] + 1, now()->endOfDay());
            }

            return redirect()
                ->route('dream-interpretation.index')
                ->with('dream_interpretation', $result['interpretation']);
        }

        return redirect()
            ->route('dream-interpretation.index')
            ->withInput($request->only('dream'))
            ->with('dream_error', $result['message'] ?? 'เกิดข้อผิดพลาด');
    }
}
