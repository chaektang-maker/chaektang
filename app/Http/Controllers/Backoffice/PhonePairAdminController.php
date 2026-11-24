<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\PhonePairMeaning;
use App\Services\PhoneNumberAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhonePairAdminController extends Controller
{
    public function index(PhoneNumberAnalysisService $service)
    {
        PhonePairMeaning::ensureTableExists();

        $pairs = $service->getAllPairsForAdmin();

        $stats = [
            'total' => count($pairs),
            'custom_count' => count(array_filter($pairs, fn ($p) => $p['is_custom'])),
            'excellent_count' => count(array_filter($pairs, fn ($p) => $p['level'] === 'excellent')),
            'good_count' => count(array_filter($pairs, fn ($p) => $p['level'] === 'good')),
            'neutral_count' => count(array_filter($pairs, fn ($p) => $p['level'] === 'neutral')),
            'bad_count' => count(array_filter($pairs, fn ($p) => $p['level'] === 'bad')),
        ];

        return Inertia::render('Backoffice/PhonePairs/Index', [
            'pairs' => $pairs,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, string $pair)
    {
        if (!preg_match('/^[0-9]{2}$/', $pair)) {
            abort(404);
        }

        $validated = $request->validate([
            'level' => 'required|in:excellent,good,neutral,bad',
            'score' => 'required|integer|min:1|max:100',
            'title' => 'required|string|max:255',
            'meaning' => 'required|string|max:2000',
            'wealth_score' => 'required|integer|min:1|max:100',
            'career_score' => 'required|integer|min:1|max:100',
            'charm_score' => 'required|integer|min:1|max:100',
            'health_score' => 'required|integer|min:1|max:100',
        ]);

        PhonePairMeaning::ensureTableExists();

        PhonePairMeaning::updateOrCreate(
            ['pair' => $pair],
            $validated
        );

        return redirect()->back()->with('success', "บันทึกคำทำนายคู่เลข {$pair} ลงฐานข้อมูลเรียบร้อยแล้ว");
    }

    public function reset(string $pair)
    {
        if (!preg_match('/^[0-9]{2}$/', $pair)) {
            abort(404);
        }

        PhonePairMeaning::ensureTableExists();

        PhonePairMeaning::where('pair', $pair)->delete();

        return redirect()->back()->with('success', "รีเซ็ตคู่เลข {$pair} กลับไปใช้ตำรามาตรฐานของระบบเรียบร้อยแล้ว");
    }
}
