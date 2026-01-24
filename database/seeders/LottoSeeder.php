<?php

namespace Database\Seeders;

use App\Models\LotteryNumber;
use App\Models\LotteryResult;
use App\Models\Source;
use Illuminate\Database\Seeder;

class LottoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drawDate = now()->toDateString();

        $sources = collect([
            [
                'name' => 'สำนัก A',
                'description' => 'สายเน้นสถิติ อัปเดตไว',
                'status' => 'active',
                'popularity_score' => 120,
                'is_promoted' => true,
            ],
            [
                'name' => 'สำนัก B',
                'description' => 'เน้น 2 ตัว',
                'status' => 'active',
                'popularity_score' => 80,
                'is_promoted' => false,
            ],
            [
                'name' => 'สำนัก C',
                'description' => 'เน้น 3 ตัว + วิ่ง',
                'status' => 'active',
                'popularity_score' => 60,
                'is_promoted' => false,
            ],
            [
                'name' => 'สำนัก D',
                'description' => 'ถูกระงับ (ตัวอย่าง)',
                'status' => 'suspended',
                'popularity_score' => 999,
                'is_promoted' => false,
            ],
        ])->map(fn ($s) => Source::firstOrCreate(['name' => $s['name']], $s));

        // ผลหวยตัวอย่าง 1 งวด
        LotteryResult::firstOrCreate(
            ['draw_date' => $drawDate],
            [
                'first_prize' => '123456',
                'last_two_digit' => '12',
                'last_three_digit' => '123',
                'running_numbers' => ['1', '2', '3'],
                'is_calculated' => false,
            ]
        );

        // เลขเด็ดของแต่ละสำนัก (เฉพาะ active จะไปโชว์หน้า public)
        foreach ($sources as $source) {
            LotteryNumber::create([
                'source_id' => $source->id,
                'draw_date' => $drawDate,
                'two_digit' => str_pad((string)random_int(0, 99), 2, '0', STR_PAD_LEFT),
                'three_digit' => str_pad((string)random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'running_numbers' => collect(range(0, 9))->shuffle()->take(3)->values()->map(fn ($n) => (string)$n)->all(),
            ]);
        }
    }
}
