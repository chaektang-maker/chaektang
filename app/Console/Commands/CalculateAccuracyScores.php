<?php

namespace App\Console\Commands;

use App\Services\AccuracyCalculationService;
use App\Models\LotteryResult;
use Illuminate\Console\Command;

class CalculateAccuracyScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lotto:calculate-accuracy {draw_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'คำนวณคะแนนความแม่นยำสำหรับงวดที่กำหนด (หรืองวดล่าสุดที่ยังไม่ได้คำนวณ)';

    /**
     * Execute the console command.
     */
    public function handle(AccuracyCalculationService $service)
    {
        $drawDate = $this->argument('draw_date');

        if (!$drawDate) {
            // หางวดล่าสุดที่ยังไม่ได้คำนวณ
            $result = LotteryResult::where('is_calculated', false)
                ->orderBy('draw_date', 'desc')
                ->first();

            if (!$result) {
                $this->info('ไม่พบงวดที่ยังไม่ได้คำนวณ');
                return;
            }

            $drawDate = $result->draw_date;
        }

        try {
            $this->info("กำลังคำนวณคะแนนสำหรับงวด: {$drawDate}");
            $service->calculateForDraw($drawDate);
            $this->info("คำนวณเสร็จสิ้น!");
        } catch (\Exception $e) {
            $this->error("เกิดข้อผิดพลาด: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
