<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\LottoAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LottoAnalyzerController extends Controller
{
    /**
     * แสดงหน้าวิเคราะห์เลขสลาก
     */
    public function index(Request $request)
    {
        return Inertia::render('LottoAnalyzer/Index', [
            'analysisResult' => session('analysis_result'),
            'analysisError' => session('analysis_error'),
            'oldNumber' => $request->old('number'),
        ]);
    }

    /**
     * ส่งข้อมูลวิเคราะห์ตัวเลขสลาก
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'number' => 'required|string|regex:/^[0-9]{6}$/',
        ], [
            'number.required' => 'กรุณากรอกตัวเลขที่ต้องการวิเคราะห์',
            'number.regex' => 'กรุณากรอกตัวเลขให้ครบ 6 หลัก และเป็นตัวเลขเท่านั้น (0-9)',
        ]);

        $service = app(LottoAnalysisService::class);
        $result = $service->analyzeNumber($request->input('number'));

        if ($result['success']) {
            return redirect()
                ->route('lotto-analyzer.index')
                ->with('analysis_result', $result);
        }

        return redirect()
            ->route('lotto-analyzer.index')
            ->withInput($request->only('number'))
            ->with('analysis_error', $result['error'] ?? 'เกิดข้อผิดพลาด');
    }
}
