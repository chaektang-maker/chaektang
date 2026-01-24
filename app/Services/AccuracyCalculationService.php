<?php

namespace App\Services;

use App\Models\LotteryResult;
use App\Models\LotteryNumber;
use App\Models\AccuracyHistory;
use App\Models\AccuracyScore;
use Illuminate\Support\Facades\DB;

class AccuracyCalculationService
{
    /**
     * คำนวณคะแนนความแม่นยำสำหรับงวดที่กำหนด
     */
    public function calculateForDraw(string $drawDate): void
    {
        $result = LotteryResult::where('draw_date', $drawDate)->first();
        
        if (!$result) {
            throw new \Exception("ไม่พบผลหวยสำหรับงวดวันที่: {$drawDate}");
        }

        if ($result->is_calculated) {
            return; // คำนวณไปแล้ว
        }

        DB::transaction(function () use ($result, $drawDate) {
            // ดึงเลขทั้งหมดของงวดนี้
            $numbers = LotteryNumber::where('draw_date', $drawDate)
                ->with('source')
                ->get();

            foreach ($numbers as $number) {
                // คำนวณ 2 ตัว
                if ($number->two_digit) {
                    $this->calculateType(
                        $number->source_id,
                        $drawDate,
                        'two_digit',
                        $number->two_digit,
                        $result->last_two_digit
                    );
                }

                // คำนวณ 3 ตัว
                if ($number->three_digit) {
                    $this->calculateType(
                        $number->source_id,
                        $drawDate,
                        'three_digit',
                        $number->three_digit,
                        $result->last_three_digit
                    );
                }

                // คำนวณเลขวิ่ง
                if ($number->running_numbers && is_array($number->running_numbers)) {
                    $runningActual = $result->running_numbers ?? [];
                    $this->calculateRunning(
                        $number->source_id,
                        $drawDate,
                        $number->running_numbers,
                        $runningActual
                    );
                }
            }

            // อัปเดต accuracy_scores สำหรับทุกสำนัก
            $this->updateAccuracyScores();

            // ตั้งค่า is_calculated = true
            $result->update(['is_calculated' => true]);
        });
    }

    /**
     * คำนวณสำหรับ 2 ตัว หรือ 3 ตัว
     */
    private function calculateType(
        int $sourceId,
        string $drawDate,
        string $type,
        string $predicted,
        string $actual
    ): void {
        $isCorrect = $predicted === $actual;

        // บันทึกประวัติ
        AccuracyHistory::updateOrCreate(
            [
                'source_id' => $sourceId,
                'draw_date' => $drawDate,
                'type' => $type,
            ],
            [
                'is_correct' => $isCorrect,
                'predicted_number' => $predicted,
                'actual_number' => $actual,
            ]
        );
    }

    /**
     * คำนวณเลขวิ่ง (ถ้าเลขที่ให้ตรงกับเลขใน running_numbers)
     */
    private function calculateRunning(
        int $sourceId,
        string $drawDate,
        array $predicted,
        array $actual
    ): void {
        $isCorrect = !empty(array_intersect($predicted, $actual));

        // บันทึกประวัติ (เก็บเป็น JSON)
        AccuracyHistory::updateOrCreate(
            [
                'source_id' => $sourceId,
                'draw_date' => $drawDate,
                'type' => 'running',
            ],
            [
                'is_correct' => $isCorrect,
                'predicted_number' => json_encode($predicted),
                'actual_number' => json_encode($actual),
            ]
        );
    }

    /**
     * อัปเดต accuracy_scores สำหรับทุกสำนัก
     */
    private function updateAccuracyScores(): void
    {
        $sources = \App\Models\Source::where('status', 'active')->get();

        foreach ($sources as $source) {
            foreach (['two_digit', 'three_digit', 'running'] as $type) {
                $histories = AccuracyHistory::where('source_id', $source->id)
                    ->where('type', $type)
                    ->orderBy('draw_date', 'desc')
                    ->get();

                $totalDraws = $histories->count();
                $correctCount = $histories->where('is_correct', true)->count();
                $accuracyPercentage = $totalDraws > 0 
                    ? round(($correctCount / $totalDraws) * 100, 2) 
                    : 0;

                // นับ consecutive correct
                $consecutive = 0;
                foreach ($histories as $history) {
                    if ($history->is_correct) {
                        $consecutive++;
                    } else {
                        break;
                    }
                }

                $lastDrawDate = $histories->first()?->draw_date;

                AccuracyScore::updateOrCreate(
                    [
                        'source_id' => $source->id,
                        'type' => $type,
                    ],
                    [
                        'total_draws' => $totalDraws,
                        'correct_count' => $correctCount,
                        'accuracy_percentage' => $accuracyPercentage,
                        'consecutive_correct' => $consecutive,
                        'last_calculated_draw_date' => $lastDrawDate,
                    ]
                );
            }
        }
    }
}
