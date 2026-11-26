<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DreamInterpretation extends Model
{
    protected $table = 'dream_interpretations';

    protected $fillable = [
        'keyword',
        'category_letter',
        'meaning',
        'two_digits',
        'three_digits',
        'no_number_note',
        'numbers_raw',
        'search_count',
    ];

    /**
     * สร้างตารางอัตโนมัติหากยังไม่ได้รัน migrate และนำเข้าข้อมูลเริ่มต้นจากไฟล์ คำทำนาย.txt หากตารางยังว่าง
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('dream_interpretations')) {
                Schema::create('dream_interpretations', function (Blueprint $table) {
                    $table->id();
                    $table->string('keyword')->unique();
                    $table->string('category_letter', 10)->index();
                    $table->text('meaning');
                    $table->string('two_digits', 500)->nullable();
                    $table->string('three_digits', 500)->nullable();
                    $table->string('no_number_note', 500)->nullable();
                    $table->string('numbers_raw', 1000)->nullable();
                    $table->unsignedInteger('search_count')->default(0);
                    $table->timestamps();
                });
            }

            if (self::query()->count() === 0) {
                self::syncFromDefaultTextFile();
            }
        } catch (\Throwable $e) {
            // กรณีฐานข้อมูลไม่พร้อมใช้งาน
        }
    }

    /**
     * นำเข้าข้อมูลจากไฟล์ คำทำนาย.txt ในโฟลเดอร์หลักของโปรเจกต์
     */
    public static function syncFromDefaultTextFile(): int
    {
        $path = base_path('คำทำนาย.txt');
        if (!is_file($path)) {
            return 0;
        }

        $content = @file_get_contents($path);
        if (!$content) {
            return 0;
        }

        return self::importFromBulkText($content);
    }

    /**
     * แยกพยัญชนะไทยตัวแรกของคำค้นหา (ก - ฮ) เพื่อใช้จัดหมวดสารบัญ
     */
    public static function extractThaiLetter(string $keyword): string
    {
        if (preg_match('/[ก-ฮ]/u', $keyword, $m)) {
            return $m[0];
        }
        return mb_substr(trim($keyword), 0, 1) ?: 'ก';
    }

    /**
     * แยกเลข 2 ตัว, เลข 3 ตัว และข้อความหมายเหตุออกจากบรรทัด "เลขมงคล ..."
     *
     * @return array{two_digits: string, three_digits: string, no_number_note: ?string, numbers_raw: string}
     */
    public static function parseLuckyNumbersLine(string $line): array
    {
        $clean = trim(preg_replace('/^(เลขมงคล|เลขเด็ด|เลขนำโชค)\s*[:：]?\s*/u', '', trim($line)));

        preg_match_all('/\b\d+\b/u', $clean, $matches);
        $numbers = $matches[0] ?? [];

        $twoDigits = [];
        $threeDigits = [];

        foreach ($numbers as $num) {
            $len = strlen($num);
            if ($len === 2 && !in_array($num, $twoDigits, true)) {
                $twoDigits[] = $num;
            } elseif ($len === 3 && !in_array($num, $threeDigits, true)) {
                $threeDigits[] = $num;
            }
        }

        // ถ้าในบรรทัดไม่มีเลข 2 ตัวหรือ 3 ตัวเลย (เช่น "ถือว่าไม่มีเลขเสี่ยงโชค จึงไม่ควรเสี่ยงโชคใดๆ ในช่วงเวลาประมาณ 3 เดือน...")
        $noNumberNote = null;
        if (empty($twoDigits) && empty($threeDigits) && $clean !== '') {
            $noNumberNote = $clean;
        }

        return [
            'two_digits' => implode(', ', $twoDigits),
            'three_digits' => implode(', ', $threeDigits),
            'no_number_note' => $noNumberNote,
            'numbers_raw' => $clean,
        ];
    }

    /**
     * แปลงข้อความชุดใหญ่ (Bulk Text รูปแบบเดียวกับ คำทำนาย.txt) ออกเป็นรายการคำทำนาย
     *
     * รูปแบบต่อ 1 รายการ:
     *   บรรทัดที่ 1: คำหลัก (เช่น กงเต๊ก)
     *   บรรทัดกลาง: เนื้อหาคำทำนาย (1 หรือหลายบรรทัด)
     *   บรรทัดสุดท้ายของรายการ: ขึ้นต้นด้วย "เลขมงคล ..."
     *
     * @return array<int, array{keyword: string, category_letter: string, meaning: string, two_digits: string, three_digits: string, no_number_note: ?string, numbers_raw: string}>
     */
    public static function parseBulkText(string $rawText): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($rawText));
        // ลบ BOM ถ้ามี
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        if ($text === '') {
            return [];
        }

        $lines = explode("\n", $text);
        $blocks = [];
        $currentLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            $currentLines[] = $trimmed;

            // เมื่อเจอบรรทัดที่ขึ้นต้นด้วย "เลขมงคล" หรือ "เลขเด็ด" ถือว่าจบ 1 คำทำนายทันที
            if (preg_match('/^(เลขมงคล|เลขเด็ด|เลขนำโชค)(\s|$|[:：])/u', $trimmed)) {
                if (count($currentLines) >= 2) {
                    $blocks[] = $currentLines;
                }
                $currentLines = [];
            }
        }

        // กรณีบล็อกสุดท้ายไม่มีคำว่า "เลขมงคล" แต่มีอย่างน้อย 2 บรรทัด
        if (count($currentLines) >= 2) {
            $blocks[] = $currentLines;
        }

        $entries = [];
        foreach ($blocks as $blockLines) {
            $keyword = trim(array_shift($blockLines));
            if ($keyword === '') {
                continue;
            }

            $lastLine = end($blockLines);
            $parsedNumbers = [
                'two_digits' => '',
                'three_digits' => '',
                'no_number_note' => null,
                'numbers_raw' => '',
            ];

            if ($lastLine !== false && preg_match('/^(เลขมงคล|เลขเด็ด|เลขนำโชค)(\s|$|[:：])/u', $lastLine)) {
                array_pop($blockLines);
                $parsedNumbers = self::parseLuckyNumbersLine($lastLine);
            }

            $meaning = trim(implode("\n", $blockLines));
            if ($meaning === '') {
                continue;
            }

            $entries[] = [
                'keyword' => $keyword,
                'category_letter' => self::extractThaiLetter($keyword),
                'meaning' => $meaning,
                'two_digits' => $parsedNumbers['two_digits'],
                'three_digits' => $parsedNumbers['three_digits'],
                'no_number_note' => $parsedNumbers['no_number_note'],
                'numbers_raw' => $parsedNumbers['numbers_raw'],
            ];
        }

        return $entries;
    }

    /**
     * นำเข้าข้อความชุดใหญ่ลงฐานข้อมูล (Update or Create ตาม keyword)
     */
    public static function importFromBulkText(string $rawText): int
    {
        $entries = self::parseBulkText($rawText);
        $count = 0;

        foreach ($entries as $entry) {
            self::updateOrCreate(
                ['keyword' => $entry['keyword']],
                [
                    'category_letter' => $entry['category_letter'],
                    'meaning' => $entry['meaning'],
                    'two_digits' => $entry['two_digits'] ?: null,
                    'three_digits' => $entry['three_digits'] ?: null,
                    'no_number_note' => $entry['no_number_note'],
                    'numbers_raw' => $entry['numbers_raw'] ?: null,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * คืนค่าอาร์เรย์เลข 2 ตัว
     */
    public function getTwoDigitArrayAttribute(): array
    {
        if (!$this->two_digits) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->two_digits))));
    }

    /**
     * คืนค่าอาร์เรย์เลข 3 ตัว
     */
    public function getThreeDigitArrayAttribute(): array
    {
        if (!$this->three_digits) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->three_digits))));
    }
}
