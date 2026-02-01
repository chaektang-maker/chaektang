<?php

namespace App\Services;

use App\Models\LotteryResult;
use App\Models\LotteryNumber;
use App\Models\SourceAccuracyRecord;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

/**
 * คำนวณ Track Record ต่อสำนักต่องวด: เข้าตรง / ตัวกลับ / เฉียด
 * - เข้าตรง: เลขที่สำนักให้ตรงกับผล (2 ตัว, 3 ตัว, เลขวิ่ง)
 * - ตัวกลับ: เลข 2/3 ตัวกลับหลักแล้วตรง (เช่น 12 → 21)
 * - เฉียด: ตรงอย่างน้อย 1 หลัก (ตำแหน่งเดียวกัน) แต่ไม่ตรง/ไม่กลับ
 */
class SourceTrackRecordService
{
    public function calculateForDraw(string $drawDate): void
    {
        $result = LotteryResult::where('draw_date', $drawDate)->first();
        if (!$result) {
            return;
        }

        $numbers = LotteryNumber::where('draw_date', $drawDate)->with('source')->get();

        foreach ($numbers as $number) {
            $hitDirect = 0;
            $hitReverse = 0;
            $hitNear = 0;
            $totalPredictions = 0;

            // 2 ตัว
            if (!empty($number->two_digit) && strlen($number->two_digit) >= 2) {
                $totalPredictions++;
                $classification = $this->classifyTwoOrThree($number->two_digit, $result->last_two_digit);
                if ($classification === 'direct') $hitDirect++;
                elseif ($classification === 'reverse') $hitReverse++;
                elseif ($classification === 'near') $hitNear++;
            }

            // 3 ตัว
            if (!empty($number->three_digit) && strlen($number->three_digit) >= 3) {
                $totalPredictions++;
                $classification = $this->classifyTwoOrThree($number->three_digit, $result->last_three_digit);
                if ($classification === 'direct') $hitDirect++;
                elseif ($classification === 'reverse') $hitReverse++;
                elseif ($classification === 'near') $hitNear++;
            }

            // เลขวิ่ง: นับเป็น direct เท่านั้น (ไม่มีตัวกลับ/เฉียดที่นิยามชัด)
            $runningActual = $result->running_numbers ?? [];
            if (!empty($number->running_numbers) && is_array($number->running_numbers)) {
                foreach ($number->running_numbers as $pred) {
                    $p = (string) $pred;
                    if ($p === '') continue;
                    $totalPredictions++;
                    if (in_array($p, $runningActual)) {
                        $hitDirect++;
                    }
                }
            }

            $hasDirect = $hitDirect > 0;
            $hasReverse = $hitReverse > 0;
            $hasNear = $hitNear > 0;

            SourceAccuracyRecord::updateOrCreate(
                [
                    'source_id' => $number->source_id,
                    'draw_date' => $drawDate,
                ],
                [
                    'hit_direct_count' => $hitDirect,
                    'hit_reverse_count' => $hitReverse,
                    'hit_near_count' => $hitNear,
                    'total_predictions' => $totalPredictions,
                    'has_direct_hit' => $hasDirect,
                    'has_reverse_hit' => $hasReverse,
                    'has_near_hit' => $hasNear,
                ]
            );
        }
    }

    /**
     * จำแนกการเข้า: direct / reverse / near / none
     * - direct: ตรงทุกหลัก
     * - reverse: กลับหลักแล้วตรง (12 vs 21, 123 vs 321)
     * - near: ตรงอย่างน้อย 1 หลัก (ตำแหน่งเดียวกัน) แต่ไม่ตรง/ไม่กลับ
     */
    public function classifyTwoOrThree(string $predicted, string $actual): string
    {
        $predicted = $this->normalizeDigits($predicted);
        $actual = $this->normalizeDigits($actual);
        $len = strlen($predicted);
        if ($len !== strlen($actual)) {
            return 'none';
        }
        if ($predicted === $actual) {
            return 'direct';
        }
        $reversed = strrev($actual);
        if ($predicted === $reversed) {
            return 'reverse';
        }
        $samePositionCount = 0;
        for ($i = 0; $i < $len; $i++) {
            if (isset($predicted[$i], $actual[$i]) && $predicted[$i] === $actual[$i]) {
                $samePositionCount++;
            }
        }
        if ($samePositionCount >= 1) {
            return 'near';
        }
        return 'none';
    }

    private function normalizeDigits(string $s): string
    {
        $s = preg_replace('/\D/', '', $s);
        return $s;
    }

    /**
     * สถิติ 10 งวดล่าสุดต่อสำนัก + สำนักแนะนำประจำงวด (สถิติดีติด 3 งวดล่าสุด)
     * @param int $lastDraws จำนวนงวดย้อนหลัง (default 10)
     * @return array{track_records: array, recommended_source_ids: array}
     */
    public function getTrackRecordRanking(int $lastDraws = 10): array
    {
        $drawDates = SourceAccuracyRecord::query()
            ->select('draw_date')
            ->distinct()
            ->orderByDesc('draw_date')
            ->limit($lastDraws + 5) // เลือกมากหน่อยแล้วตัด
            ->pluck('draw_date');

        $records = SourceAccuracyRecord::query()
            ->whereIn('draw_date', $drawDates)
            ->with('source')
            ->orderByDesc('draw_date')
            ->get();

        $bySource = $records->groupBy('source_id');
        $orderedDrawDates = $drawDates->take($lastDraws)->values();

        $trackRecords = [];
        $recommendedSourceIds = [];

        $sources = Source::where('status', 'active')->get();
        foreach ($sources as $source) {
            $sourceRecords = $bySource->get($source->id, collect())
                ->keyBy('draw_date')
                ->sortByDesc('draw_date')
                ->values();

            $last10 = $sourceRecords->take($lastDraws);
            $drawsDirect = $last10->where('has_direct_hit', true)->count();
            $drawsReverse = $last10->where('has_reverse_hit', true)->count();
            $drawsNear = $last10->where('has_near_hit', true)->count();
            $totalHitDirect = $last10->sum('hit_direct_count');
            $totalHitReverse = $last10->sum('hit_reverse_count');
            $totalHitNear = $last10->sum('hit_near_count');
            $totalPredictions = $last10->sum('total_predictions');

            $last3 = $sourceRecords->take(3);
            $consecutiveGood = 0;
            foreach ($last3 as $r) {
                if ($r->has_direct_hit) {
                    $consecutiveGood++;
                } else {
                    break;
                }
            }
            $isRecommended = $consecutiveGood >= 3;

            if ($isRecommended) {
                $recommendedSourceIds[] = $source->id;
            }

            $trackRecords[] = [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'draws_direct' => $drawsDirect,
                'draws_reverse' => $drawsReverse,
                'draws_near' => $drawsNear,
                'total_hit_direct' => $totalHitDirect,
                'total_hit_reverse' => $totalHitReverse,
                'total_hit_near' => $totalHitNear,
                'total_predictions' => $totalPredictions,
                'last_10_draws_count' => $last10->count(),
                'is_recommended' => $isRecommended,
                'consecutive_direct_draws' => $consecutiveGood,
            ];
        }

        // เรียง: แนะนำก่อน แล้วตามด้วยจำนวนงวดที่เข้าตรง (มากก่อน)
        usort($trackRecords, function ($a, $b) {
            if ($a['is_recommended'] !== $b['is_recommended']) {
                return $a['is_recommended'] ? -1 : 1;
            }
            if ($a['draws_direct'] !== $b['draws_direct']) {
                return $b['draws_direct'] <=> $a['draws_direct'];
            }
            return $b['draws_reverse'] <=> $a['draws_reverse'];
        });

        return [
            'track_records' => $trackRecords,
            'recommended_source_ids' => $recommendedSourceIds,
            'last_draw_dates' => $orderedDrawDates->toArray(),
        ];
    }
}
