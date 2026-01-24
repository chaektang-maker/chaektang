<?php

namespace App\Services;

use App\Models\LottoData;
use App\Models\LottoPrize;
use App\Models\LottoRunningNumber;
use Illuminate\Support\Facades\DB;

class LottoStatisticsService
{
    /**
     * สถิติเลขท้าย 2 ตัวที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopLastTwoDigits(int $limit = 10): array
    {
        $runningNumbers = LottoRunningNumber::where('running_id', 'runningNumberBackTwo')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($runningNumbers as $running) {
            if (is_array($running->numbers) && !empty($running->numbers)) {
                $number = $running->numbers[0];
                if (isset($counts[$number])) {
                    $counts[$number]++;
                } else {
                    $counts[$number] = 1;
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        return array_map(function ($number, $count) {
            return [
                'number' => str_pad($number, 2, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }

    /**
     * สถิติเลขท้าย 3 ตัวที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopLastThreeDigits(int $limit = 10): array
    {
        $runningNumbers = LottoRunningNumber::where('running_id', 'runningNumberBackThree')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($runningNumbers as $running) {
            if (is_array($running->numbers) && !empty($running->numbers)) {
                $number = $running->numbers[0];
                if (isset($counts[$number])) {
                    $counts[$number]++;
                } else {
                    $counts[$number] = 1;
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        return array_map(function ($number, $count) {
            return [
                'number' => str_pad($number, 3, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }

    /**
     * สถิติเลขหน้า 3 ตัวที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopFrontThreeDigits(int $limit = 10): array
    {
        $runningNumbers = LottoRunningNumber::where('running_id', 'runningNumberFrontThree')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($runningNumbers as $running) {
            if (is_array($running->numbers)) {
                foreach ($running->numbers as $number) {
                    if (isset($counts[$number])) {
                        $counts[$number]++;
                    } else {
                        $counts[$number] = 1;
                    }
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        return array_map(function ($number, $count) {
            return [
                'number' => str_pad($number, 3, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }

    /**
     * สถิติเลขตัวแรกของรางวัลที่ 1 ที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopFirstPrizeFirstDigits(int $limit = 10): array
    {
        $prizes = LottoPrize::where('prize_id', 'prizeFirst')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($prizes as $prize) {
            if (is_array($prize->numbers) && !empty($prize->numbers)) {
                $firstDigit = substr($prize->numbers[0], 0, 1);
                if (isset($counts[$firstDigit])) {
                    $counts[$firstDigit]++;
                } else {
                    $counts[$firstDigit] = 1;
                }
            }
        }

        arsort($counts);
        $topDigits = array_slice($counts, 0, $limit, true);

        return array_map(function ($digit, $count) {
            return [
                'digit' => $digit,
                'count' => $count,
            ];
        }, array_keys($topDigits), $topDigits);
    }

    /**
     * สถิติเลขตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopFirstPrizeLastDigits(int $limit = 10): array
    {
        $prizes = LottoPrize::where('prize_id', 'prizeFirst')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($prizes as $prize) {
            if (is_array($prize->numbers) && !empty($prize->numbers)) {
                $lastDigit = substr($prize->numbers[0], -1);
                if (isset($counts[$lastDigit])) {
                    $counts[$lastDigit]++;
                } else {
                    $counts[$lastDigit] = 1;
                }
            }
        }

        arsort($counts);
        $topDigits = array_slice($counts, 0, $limit, true);

        return array_map(function ($digit, $count) {
            return [
                'digit' => $digit,
                'count' => $count,
            ];
        }, array_keys($topDigits), $topDigits);
    }

    /**
     * สถิติเลข 2 ตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopFirstPrizeLastTwoDigits(int $limit = 10): array
    {
        $prizes = LottoPrize::where('prize_id', 'prizeFirst')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($prizes as $prize) {
            if (is_array($prize->numbers) && !empty($prize->numbers)) {
                $lastTwo = substr($prize->numbers[0], -2);
                if (isset($counts[$lastTwo])) {
                    $counts[$lastTwo]++;
                } else {
                    $counts[$lastTwo] = 1;
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        return array_map(function ($number, $count) {
            return [
                'number' => str_pad($number, 2, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }

    /**
     * สถิติเลข 3 ตัวสุดท้ายของรางวัลที่ 1 ที่ออกบ่อยที่สุด
     * 
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getTopFirstPrizeLastThreeDigits(int $limit = 10): array
    {
        $prizes = LottoPrize::where('prize_id', 'prizeFirst')
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($prizes as $prize) {
            if (is_array($prize->numbers) && !empty($prize->numbers)) {
                $lastThree = substr($prize->numbers[0], -3);
                if (isset($counts[$lastThree])) {
                    $counts[$lastThree]++;
                } else {
                    $counts[$lastThree] = 1;
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        return array_map(function ($number, $count) {
            return [
                'number' => str_pad($number, 3, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }

    /**
     * สถิติรวมทั้งหมด
     * 
     * @return array
     */
    public function getAllStatistics(): array
    {
        $totalLottos = LottoData::where('is_fetched', 1)->count();
        $totalPrizes = LottoPrize::where('prize_id', 'prizeFirst')->count();

        return [
            'total_lottos' => $totalLottos,
            'total_prizes' => $totalPrizes,
            'top_last_two_digits' => $this->getTopLastTwoDigits(20),
            'top_last_three_digits' => $this->getTopLastThreeDigits(20),
            'top_front_three_digits' => $this->getTopFrontThreeDigits(20),
            'top_first_prize_first_digits' => $this->getTopFirstPrizeFirstDigits(10),
            'top_first_prize_last_digits' => $this->getTopFirstPrizeLastDigits(10),
            'top_first_prize_last_two_digits' => $this->getTopFirstPrizeLastTwoDigits(20),
            'top_first_prize_last_three_digits' => $this->getTopFirstPrizeLastThreeDigits(20),
        ];
    }

    /**
     * สถิติเลขที่ออกบ่อยในช่วงปีที่กำหนด
     * 
     * @param int $startYear ปีเริ่มต้น (พ.ศ.)
     * @param int $endYear ปีสิ้นสุด (พ.ศ.)
     * @param string $type ประเภท: 'last_two', 'last_three', 'front_three'
     * @param int $limit จำนวนที่ต้องการ
     * @return array
     */
    public function getStatisticsByYearRange(int $startYear, int $endYear, string $type = 'last_two', int $limit = 10): array
    {
        // ดึงข้อมูล lotto_data ที่อยู่ในช่วงปีที่กำหนด
        $lottoIds = LottoData::where('is_fetched', 1)
            ->where(function($query) use ($startYear, $endYear) {
                // สร้าง array ของปีทั้งหมดในช่วง
                $years = [];
                for ($year = $startYear; $year <= $endYear; $year++) {
                    $years[] = $year;
                }
                
                $query->where(function($q) use ($years) {
                    foreach ($years as $index => $year) {
                        if ($index === 0) {
                            $q->where('date_text', 'like', "%{$year}%");
                        } else {
                            $q->orWhere('date_text', 'like', "%{$year}%");
                        }
                    }
                });
            })
            ->pluck('lotto_id')
            ->toArray();

        if (empty($lottoIds)) {
            return [];
        }

        $runningId = 'runningNumberBackTwo';
        switch ($type) {
            case 'last_two':
                $runningId = 'runningNumberBackTwo';
                break;
            case 'last_three':
                $runningId = 'runningNumberBackThree';
                break;
            case 'front_three':
                $runningId = 'runningNumberFrontThree';
                break;
        }

        $runningNumbers = LottoRunningNumber::where('running_id', $runningId)
            ->whereIn('lotto_id', $lottoIds)
            ->whereNotNull('numbers')
            ->get();

        $counts = [];
        foreach ($runningNumbers as $running) {
            if (is_array($running->numbers)) {
                $numbers = $type === 'front_three' ? $running->numbers : [$running->numbers[0] ?? null];
                foreach ($numbers as $number) {
                    if ($number !== null) {
                        if (isset($counts[$number])) {
                            $counts[$number]++;
                        } else {
                            $counts[$number] = 1;
                        }
                    }
                }
            }
        }

        arsort($counts);
        $topNumbers = array_slice($counts, 0, $limit, true);

        $padding = $type === 'last_two' ? 2 : 3;
        return array_map(function ($number, $count) use ($padding) {
            return [
                'number' => str_pad($number, $padding, '0', STR_PAD_LEFT),
                'count' => $count,
            ];
        }, array_keys($topNumbers), $topNumbers);
    }
}
