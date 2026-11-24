<?php

namespace App\Services;

use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Support\Facades\Log;

class LottoAnalysisService
{
    /**
     * วิเคราะห์ตัวเลข 6 หลักตามสถิติและคณิตศาสตร์
     * 
     * @param string $number เลขสลาก 6 หลัก
     * @return array ผลลัพธ์การวิเคราะห์อย่างละเอียด
     */
    public function analyzeNumber(string $number): array
    {
        // 0. ตรวจสอบความถูกต้องเบื้องต้น
        if (!preg_match('/^[0-9]{6}$/', $number)) {
            return [
                'success' => false,
                'error' => 'ตัวเลขต้องเป็นตัวเลข 6 หลักเท่านั้น'
            ];
        }

        $digits = str_split($number);
        $digits = array_map('intval', $digits);
        
        $sum = array_sum($digits);
        
        // 1. คำนวณอัตราส่วน คู่/คี่ (Odd/Even)
        $oddCount = 0;
        $evenCount = 0;
        foreach ($digits as $digit) {
            if ($digit % 2 === 0) {
                $evenCount++;
            } else {
                $oddCount++;
            }
        }
        
        $oddEvenScore = 2; // คะแนนต่ำสุด
        if ($oddCount === 3 && $evenCount === 3) {
            $oddEvenScore = 15;
        } elseif (($oddCount === 4 && $evenCount === 2) || ($oddCount === 2 && $evenCount === 4)) {
            $oddEvenScore = 10;
        } elseif (($oddCount === 5 && $evenCount === 1) || ($oddCount === 1 && $evenCount === 5)) {
            $oddEvenScore = 5;
        }
        
        // 2. คำนวณอัตราส่วน สูง/ต่ำ (High/Low: 0-4 ต่ำ, 5-9 สูง)
        $lowCount = 0;
        $highCount = 0;
        foreach ($digits as $digit) {
            if ($digit >= 5) {
                $highCount++;
            } else {
                $lowCount++;
            }
        }
        
        $highLowScore = 2;
        if ($highCount === 3 && $lowCount === 3) {
            $highLowScore = 15;
        } elseif (($highCount === 4 && $lowCount === 2) || ($highCount === 2 && $lowCount === 4)) {
            $highLowScore = 10;
        } elseif (($highCount === 5 && $lowCount === 1) || ($highCount === 1 && $lowCount === 5)) {
            $highLowScore = 5;
        }

        // 3. คำนวณผลรวมตัวเลขหลัก (Digit Sum)
        $sumScore = 2;
        if ($sum >= 20 && $sum <= 40) {
            $sumScore = 20;
        } elseif (($sum >= 15 && $sum <= 19) || ($sum >= 41 && $sum <= 45)) {
            $sumScore = 12;
        } elseif (($sum >= 10 && $sum <= 14) || ($sum >= 46 && $sum <= 50)) {
            $sumScore = 6;
        }

        // 4. ตรวจจับลวดลายตัวเลขและเลขอัตลักษณ์ (Numeric Patterns)
        $patternScore = 20;
        $hasConsecutiveWhole = false;
        $hasConsecutiveSub = false;
        $hasTriple = false;
        $hasQuadruple = false;
        $hasDoubleDouble = false;

        // ตรวจสอบเลขเรียง 6 หลัก (เช่น 123456 หรือ 654321)
        $isAscending = true;
        $isDescending = true;
        for ($i = 0; $i < 5; $i++) {
            if ($digits[$i + 1] !== $digits[$i] + 1) {
                $isAscending = false;
            }
            if ($digits[$i + 1] !== $digits[$i] - 1) {
                $isDescending = false;
            }
        }
        if ($isAscending || $isDescending) {
            $hasConsecutiveWhole = true;
            $patternScore -= 15;
        }

        // ตรวจสอบเลขเรียงย่อย 3 หลัก (เช่น 123 หรือ 456)
        if (!$hasConsecutiveWhole) {
            for ($i = 0; $i < 4; $i++) {
                if (($digits[$i + 1] === $digits[$i] + 1 && $digits[$i + 2] === $digits[$i + 1] + 1) ||
                    ($digits[$i + 1] === $digits[$i] - 1 && $digits[$i + 2] === $digits[$i + 1] - 1)) {
                    $hasConsecutiveSub = true;
                }
            }
            if ($hasConsecutiveSub) {
                $patternScore -= 5;
            }
        }

        // ตรวจสอบตัวเลขซ้ำ
        $digitCounts = array_count_values($digits);
        $maxDup = max($digitCounts);
        $doubleCount = 0;

        foreach ($digitCounts as $d => $c) {
            if ($c === 2) {
                $doubleCount++;
            }
            if ($c === 3) {
                $hasTriple = true;
            }
            if ($c >= 4) {
                $hasQuadruple = true;
            }
        }

        if ($hasQuadruple) {
            $patternScore -= 12;
        } elseif ($hasTriple) {
            $patternScore -= 8;
        } elseif ($doubleCount >= 2) {
            $hasDoubleDouble = true;
            $patternScore -= 3;
        }

        $patternScore = max(2, $patternScore); // จำกัดต่ำสุดที่ 2 คะแนน

        // 5. ค้นหาความถี่เชิงประวัติศาสตร์จาก Database
        $lastTwo = substr($number, -2);
        $lastThree = substr($number, -3);
        $frontThree = substr($number, 0, 3);

        // ดึงสถิติจริง
        $firstPrizeCount = LottoPrize::where('prize_id', 'prizeFirst')
            ->whereJsonContains('numbers', $number)
            ->count();

        $lastTwoCount = LottoRunningNumber::where('running_id', 'runningNumberBackTwo')
            ->whereJsonContains('numbers', $lastTwo)
            ->count();

        $lastThreeCount = LottoRunningNumber::where('running_id', 'runningNumberBackThree')
            ->whereJsonContains('numbers', $lastThree)
            ->count();

        $frontThreeCount = LottoRunningNumber::where('running_id', 'runningNumberFrontThree')
            ->whereJsonContains('numbers', $frontThree)
            ->count();

        // คำนวณคะแนนสถิติย้อนหลัง
        $historyScore = 15; // คะแนนฐาน (เลขที่ไม่เคยออกแต่มีความเป็นไปได้ทั่วไป)

        // โบนัสสถิติออกซ้ำ
        $historyScore += min(5, $lastTwoCount * 1.5);
        $historyScore += min(5, $lastThreeCount * 2);
        $historyScore += min(5, $frontThreeCount * 2);

        // วิเคราะห์รางวัลที่ 1
        if ($firstPrizeCount > 0) {
            // เคยออกรางวัลที่ 1 แล้ว (ทางความเชื่อคือโอกาสซ้ำน้อย)
            $historyScore -= 5;
        } else {
            // เลขใหม่ ยังไม่เคยออกรางวัลที่ 1
            $historyScore += 5;
        }

        $historyScore = max(5, min(30, $historyScore));

        // 6. รวมคะแนนทั้งหมดเต็ม 100
        $totalScore = $oddEvenScore + $highLowScore + $sumScore + $patternScore + $historyScore;
        $totalScore = min(100, max(1, $totalScore));

        // 7. จัดบทวิเคราะห์สรุปเป็นข้อความภาษาไทย
        $analysisDetails = [];
        
        // วิเคราะห์ความสมดุล
        if ($oddCount === 3 && $evenCount === 3) {
            $analysisDetails[] = "ตัวเลขมีความสมบูรณ์ตามหลักสถิติเชิงธรรมชาติ โดยมีสัดส่วนเลขคู่และคี่ที่สมดุลดีเยี่ยม (3:3)";
        } else {
            $analysisDetails[] = "ตัวเลขมีสัดส่วนเลขคู่และคี่อยู่ในเกณฑ์สุ่มทั่วไป (คู่ {$evenCount} ตัว, คี่ {$oddCount} ตัว)";
        }

        if ($highCount === 3 && $lowCount === 3) {
            $analysisDetails[] = "ตัวเลขมีการกระจายตัวเลขสูง-ต่ำที่ดีมาก (สูง {$highCount} ตัว, ต่ำ {$lowCount} ตัว) ช่วยให้โครงสร้างมีความเป็นไปได้ในทางปฏิบัติ";
        }

        // วิเคราะห์ผลรวม
        $analysisDetails[] = "ผลรวมของตัวเลขทุกหลักเท่ากับ {$sum} " . ($sum >= 20 && $sum <= 40 ? "ซึ่งเป็นช่วงคะแนนกลางที่เป็นค่าเฉลี่ยปกติของสลากที่ถูกรางวัล" : "ซึ่งค่อนไปทางขอบนอกของค่าเฉลี่ยปกติเล็กน้อย");

        // วิเคราะห์โครงสร้าง
        if ($hasConsecutiveWhole) {
            $analysisDetails[] = "เป็นเลขเรียงแบบเรียงลำดับต่อเนื่องกันทั้ง 6 หลัก ซึ่งมีโอกาสสุ่มได้น้อยมากตามสถิติของกองสลาก";
        } elseif ($hasConsecutiveSub) {
            $analysisDetails[] = "มีกลุ่มเลขเรียงต่อเนื่อง 3 หลักซ่อนอยู่ภายในตัวเลขชุดนี้";
        }

        if ($hasQuadruple) {
            $analysisDetails[] = "พบกลุ่มตัวเลขซ้ำกันตั้งแต่ 4 ตัวขึ้นไป ซึ่งทำให้โอกาสกระจายตัวสุ่มลดต่ำลง";
        } elseif ($hasTriple) {
            $analysisDetails[] = "พบตัวเลขซ้ำกัน 3 ตัว ซึ่งเป็นลักษณะตัวเลขที่พบได้เป็นครั้งคราว";
        }

        // วิเคราะห์สถิติจริงจากประวัติศาสตร์
        if ($firstPrizeCount > 0) {
            $analysisDetails[] = "⚠️ ตัวเลข 6 หลักชุดนี้เคยถูกรางวัลที่ 1 มาแล้วในประวัติศาสตร์ โอกาสที่จะหมุนออกซ้ำอีกรอบสำหรับรางวัลที่ 1 มีน้อยมาก";
        } else {
            $analysisDetails[] = "✨ ตัวเลขชุดนี้ยังไม่เคยถูกออกรางวัลที่ 1 มาก่อน ถือเป็นตัวเลขชุดใหม่ที่รอลุ้นโอกาสแจ้งเกิด";
        }

        if ($lastTwoCount > 0) {
            $analysisDetails[] = "🎯 เลขท้าย 2 ตัว '{$lastTwo}' เคยถูกรางวัลเลขท้าย 2 ตัวมาแล้ว {$lastTwoCount} ครั้ง";
        } else {
            $analysisDetails[] = "เลขท้าย 2 ตัว '{$lastTwo}' ยังไม่เคยถูกสุ่มออกเป็นรางวัลเลขท้าย 2 ตัวในประวัติศาสตร์ระบบฐานข้อมูล";
        }

        if ($lastThreeCount > 0) {
            $analysisDetails[] = "เลขท้าย 3 ตัว '{$lastThree}' เคยออกรางวัลเลขท้าย 3 ตัวมาแล้ว {$lastThreeCount} ครั้ง";
        }
        
        if ($frontThreeCount > 0) {
            $analysisDetails[] = "เลขหน้า 3 ตัว '{$frontThree}' เคยออกรางวัลเลขหน้า 3 ตัวมาแล้ว {$frontThreeCount} ครั้ง";
        }

        return [
            'success' => true,
            'number' => $number,
            'score' => (int)$totalScore,
            'metrics' => [
                'odd_even' => "{$oddCount}:{$evenCount}",
                'high_low' => "{$highCount}:{$lowCount}",
                'sum' => $sum,
                'max_dup' => $maxDup,
                'first_prize_drawn' => $firstPrizeCount > 0,
                'last_two_count' => $lastTwoCount,
                'last_three_count' => $lastThreeCount,
                'front_three_count' => $frontThreeCount,
            ],
            'analysis' => $analysisDetails
        ];
    }
}
