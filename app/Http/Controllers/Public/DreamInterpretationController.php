<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\DreamInterpretationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * หน้าและ API ทำนายฝัน (ใช้ Google AI Studio / Gemini)
 */
class DreamInterpretationController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('DreamInterpretation/Index', [
            'oldDream' => $request->old('dream'),
        ]);
    }

    /**
     * รับข้อความฝันจากฟอร์ม → เรียก AI → redirect กลับพร้อมผลทำนายใน session
     */
    public function interpret(Request $request)
    {
        $request->validate([
            'dream' => 'required|string|max:2000',
        ], [
            'dream.required' => 'กรุณาพิมพ์ความฝันของคุณ',
            'dream.max' => 'ความฝันยาวเกินไป (สูงสุด 2000 ตัวอักษร)',
        ]);

        $service = app(DreamInterpretationService::class);
        $result = $service->interpret($request->input('dream'));

        if ($result['success']) {
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
