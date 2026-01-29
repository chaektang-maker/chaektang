<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LotteryResult;
use App\Services\AccuracyCalculationService;
use App\Services\LottoApiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LotteryResultController extends Controller
{
    public function index()
    {
        $results = LotteryResult::query()
            ->orderByDesc('draw_date')
            ->paginate(15);

        return Inertia::render('Admin/Results/Index', [
            'results' => $results,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Results/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'draw_date' => ['required', 'date'],
            'first_prize' => ['required', 'string', 'max:20'],
            'last_two_digit' => ['required', 'string', 'max:10'],
            'last_three_digit' => ['required', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'], // รับเป็น "1,2,3" แล้วค่อยแปลง
        ]);

        $running = null;
        if (!empty($data['running_numbers'])) {
            $running = collect(explode(',', $data['running_numbers']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->values()
                ->all();
        }

        $result = LotteryResult::create([
            'draw_date' => $data['draw_date'],
            'first_prize' => $data['first_prize'],
            'last_two_digit' => $data['last_two_digit'],
            'last_three_digit' => $data['last_three_digit'],
            'running_numbers' => $running,
            'is_calculated' => false,
        ]);

        // คำนวณคะแนนอัตโนมัติ
        try {
            app(AccuracyCalculationService::class)->calculateForDraw($result->draw_date);
        } catch (\Exception $e) {
            // ถ้าคำนวณไม่สำเร็จ ให้บันทึกผลหวยไว้ก่อน (อาจจะยังไม่มีเลขจากสำนัก)
        }

        return redirect()->route('backoffice.results.index')->with('success', 'บันทึกผลหวยสำเร็จ');
    }

    public function edit(LotteryResult $result)
    {
        return Inertia::render('Admin/Results/Edit', [
            'result' => $result,
        ]);
    }

    public function update(Request $request, LotteryResult $result)
    {
        $data = $request->validate([
            'draw_date' => ['required', 'date'],
            'first_prize' => ['required', 'string', 'max:20'],
            'last_two_digit' => ['required', 'string', 'max:10'],
            'last_three_digit' => ['required', 'string', 'max:10'],
            'running_numbers' => ['nullable', 'string'],
            'is_calculated' => ['nullable', 'boolean'],
        ]);

        $running = null;
        if (!empty($data['running_numbers'])) {
            $running = collect(explode(',', $data['running_numbers']))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->values()
                ->all();
        }

        $oldCalculated = $result->is_calculated;
        
        $result->update([
            'draw_date' => $data['draw_date'],
            'first_prize' => $data['first_prize'],
            'last_two_digit' => $data['last_two_digit'],
            'last_three_digit' => $data['last_three_digit'],
            'running_numbers' => $running,
            'is_calculated' => (bool)($data['is_calculated'] ?? false),
        ]);

        // ถ้ายังไม่ได้คำนวณ หรือแก้ไขผลหวย ให้คำนวณใหม่
        if (!$result->is_calculated || !$oldCalculated) {
            try {
                app(AccuracyCalculationService::class)->calculateForDraw($result->draw_date);
            } catch (\Exception $e) {
                // ถ้าคำนวณไม่สำเร็จ ให้บันทึกผลหวยไว้ก่อน
            }
        }

        return redirect()->route('backoffice.results.index')->with('success', 'บันทึกสำเร็จ');
    }

    public function destroy(LotteryResult $result)
    {
        $result->delete();

        return redirect()->route('backoffice.results.index')->with('success', 'ลบผลหวยสำเร็จ');
    }

    /**
     * หน้าแสดงฟอร์มสำหรับดึงข้อมูลหวยย้อนหลัง
     */
    public function importForm()
    {
        return Inertia::render('Admin/Results/Import');
    }

    /**
     * ดึงข้อมูลหวยย้อนหลังจาก API และบันทึกลง database
     */
    public function importFromApi(Request $request)
    {
        $request->validate([
            'start_page' => ['nullable', 'integer', 'min:1', 'max:23'],
            'end_page' => ['nullable', 'integer', 'min:1', 'max:23'],
        ]);

        $startPage = $request->input('start_page', 23);
        $endPage = $request->input('end_page', 1);

        // ตรวจสอบว่า start_page ต้องมากกว่า end_page
        if ($startPage < $endPage) {
            return back()->withErrors([
                'start_page' => 'หน้าเริ่มต้นต้องมากกว่าหรือเท่ากับหน้าสุดท้าย'
            ]);
        }

        try {
            $apiService = app(LottoApiService::class);
            $summary = $apiService->importHistoricalLotteries($startPage, $endPage);

            $message = sprintf(
                'ดึงข้อมูลสำเร็จ: หน้า %d-%d, รวม %d งวด, สำเร็จ %d งวด, ล้มเหลว %d งวด, ข้าม %d งวด',
                $startPage,
                $endPage,
                $summary['total_lotteries'],
                $summary['success'],
                $summary['failed'],
                $summary['skipped']
            );

            return redirect()
                ->route('admin.results.index')
                ->with('success', $message)
                ->with('import_summary', $summary);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
            ]);
        }
    }
}
