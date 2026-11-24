<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Services\PhoneNumberAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhoneAnalyzerController extends Controller
{
    public function index(Request $request, PhoneNumberAnalysisService $service)
    {
        $phoneQuery = trim($request->string('phone')->toString());
        $analysisResult = session('phone_analysis_result');
        $analysisError = session('phone_analysis_error');

        // รองรับการเปิดผ่านลิงก์ที่แชร์ เช่น /phone-analyzer?phone=0812345678
        if (!$analysisResult && $phoneQuery !== '') {
            $res = $service->analyze($phoneQuery);
            if ($res['success']) {
                $analysisResult = $res;
            } else {
                $analysisError = $res['error'] ?? null;
            }
        }

        // ดึงสินค้าสายมู (Affiliate) สำหรับแนะนำเสริมดวง / แก้เคล็ดเบอร์มือถือ
        $affiliateProducts = AffiliateProduct::query()
            ->with('platform:id,name,logo_url')
            ->where('is_active', true)
            ->whereHas('platform', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->limit(4)
            ->get(['id', 'platform_id', 'title', 'description', 'image_url', 'affiliate_url']);

        return Inertia::render('PhoneAnalyzer/Index', [
            'analysisResult' => $analysisResult,
            'analysisError' => $analysisError,
            'initialPhone' => $analysisResult['phone'] ?? ($phoneQuery ?: $request->old('phone', '')),
            'affiliateProducts' => $affiliateProducts,
        ]);
    }

    public function analyze(Request $request, PhoneNumberAnalysisService $service)
    {
        $cleanPhone = preg_replace('/\D/', '', (string) $request->input('phone', '')) ?? '';

        if (!preg_match('/^0[689]\d{8}$/', $cleanPhone)) {
            return redirect()
                ->route('phone-analyzer.index')
                ->withInput(['phone' => $request->input('phone')])
                ->with('phone_analysis_error', 'กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลักให้ถูกต้อง (ขึ้นต้นด้วย 06, 08 หรือ 09)');
        }

        $result = $service->analyze($cleanPhone);

        if ($result['success']) {
            return redirect()
                ->route('phone-analyzer.index', ['phone' => $cleanPhone])
                ->with('phone_analysis_result', $result);
        }

        return redirect()
            ->route('phone-analyzer.index')
            ->withInput(['phone' => $cleanPhone])
            ->with('phone_analysis_error', $result['error'] ?? 'เกิดข้อผิดพลาดในการวิเคราะห์เบอร์');
    }
}
