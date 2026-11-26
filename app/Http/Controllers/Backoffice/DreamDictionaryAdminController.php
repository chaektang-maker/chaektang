<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\DreamInterpretation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DreamDictionaryAdminController extends Controller
{
    public function index(Request $request)
    {
        DreamInterpretation::ensureTableExists();

        $items = DreamInterpretation::query()
            ->orderBy('keyword')
            ->get()
            ->map(function (DreamInterpretation $item) {
                return [
                    'id' => $item->id,
                    'keyword' => $item->keyword,
                    'category_letter' => $item->category_letter,
                    'meaning' => $item->meaning,
                    'two_digits' => $item->two_digits ?? '',
                    'three_digits' => $item->three_digits ?? '',
                    'no_number_note' => $item->no_number_note ?? '',
                    'numbers_raw' => $item->numbers_raw ?? '',
                    'search_count' => $item->search_count,
                ];
            });

        $letters = $items->pluck('category_letter')->unique()->sort()->values();

        return Inertia::render('Backoffice/DreamDictionary/Index', [
            'items' => $items,
            'letters' => $letters,
            'stats' => [
                'total' => $items->count(),
                'with_numbers' => $items->filter(fn ($i) => $i['two_digits'] !== '' || $i['three_digits'] !== '')->count(),
                'no_numbers' => $items->filter(fn ($i) => $i['two_digits'] === '' && $i['three_digits'] === '')->count(),
            ],
        ]);
    }

    /**
     * นำเข้าคำทำนายชุดใหญ่จากการวางข้อความ (Bulk Paste) หรืออัปโหลดไฟล์ .txt
     */
    public function importBulk(Request $request)
    {
        $request->validate([
            'raw_text' => 'nullable|string',
            'txt_file' => 'nullable|file|max:10240',
        ]);

        DreamInterpretation::ensureTableExists();

        $rawText = (string) $request->input('raw_text', '');

        if ($request->hasFile('txt_file')) {
            $fileContent = @file_get_contents($request->file('txt_file')->getRealPath());
            if ($fileContent) {
                $rawText = $fileContent . "\n\n" . $rawText;
            }
        }

        if (trim($rawText) === '') {
            return redirect()->back()->with('error', 'กรุณาวางข้อความคำทำนาย หรือเลือกไฟล์ .txt ที่ต้องการนำเข้า');
        }

        $importedCount = DreamInterpretation::importFromBulkText($rawText);

        if ($importedCount === 0) {
            return redirect()->back()->with('error', 'ไม่พบรายการคำทำนายที่ถูกต้องตามรูปแบบ กรุณาตรวจสอบรูปแบบข้อความ');
        }

        return redirect()->back()->with('success', "นำเข้าและอัปเดตคำทำนายฝันสำเร็จจำนวน {$importedCount} รายการ!");
    }

    /**
     * ดึงข้อมูลจากไฟล์ คำทำนาย.txt ในโฟลเดอร์โปรเจกต์เข้า Database ทันที
     */
    public function syncDefaultFile()
    {
        DreamInterpretation::ensureTableExists();
        $count = DreamInterpretation::syncFromDefaultTextFile();

        return redirect()->back()->with('success', "ซิงค์ข้อมูลจากไฟล์ คำทำนาย.txt สำเร็จจำนวน {$count} รายการ!");
    }

    /**
     * เพิ่มหรือบันทึกแก้ไขคำทำนายรายคำ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'keyword' => 'required|string|max:255',
            'meaning' => 'required|string|max:5000',
            'two_digits' => 'nullable|string|max:500',
            'three_digits' => 'nullable|string|max:500',
            'no_number_note' => 'nullable|string|max:500',
        ]);

        DreamInterpretation::ensureTableExists();

        $keyword = trim($validated['keyword']);
        $letter = DreamInterpretation::extractThaiLetter($keyword);

        // จัดระเบียบเลข 2 ตัว และ 3 ตัว ให้คั่นด้วย ", "
        $twoDigits = '';
        if (!empty($validated['two_digits'])) {
            preg_match_all('/\b\d{2}\b/', $validated['two_digits'], $m2);
            $twoDigits = implode(', ', array_unique($m2[0] ?? []));
        }

        $threeDigits = '';
        if (!empty($validated['three_digits'])) {
            preg_match_all('/\b\d{3}\b/', $validated['three_digits'], $m3);
            $threeDigits = implode(', ', array_unique($m3[0] ?? []));
        }

        $noNumberNote = trim((string) ($validated['no_number_note'] ?? '')) ?: null;

        if (!empty($validated['id'])) {
            $item = DreamInterpretation::findOrFail($validated['id']);
            $item->update([
                'keyword' => $keyword,
                'category_letter' => $letter,
                'meaning' => trim($validated['meaning']),
                'two_digits' => $twoDigits ?: null,
                'three_digits' => $threeDigits ?: null,
                'no_number_note' => $noNumberNote,
            ]);
            return redirect()->back()->with('success', "แก้ไขคำทำนาย '{$keyword}' เรียบร้อยแล้ว");
        }

        DreamInterpretation::updateOrCreate(
            ['keyword' => $keyword],
            [
                'category_letter' => $letter,
                'meaning' => trim($validated['meaning']),
                'two_digits' => $twoDigits ?: null,
                'three_digits' => $threeDigits ?: null,
                'no_number_note' => $noNumberNote,
            ]
        );

        return redirect()->back()->with('success', "บันทึกคำทำนาย '{$keyword}' ลงฐานข้อมูลเรียบร้อยแล้ว");
    }

    public function destroy(int $id)
    {
        DreamInterpretation::ensureTableExists();
        $item = DreamInterpretation::findOrFail($id);
        $keyword = $item->keyword;
        $item->delete();

        return redirect()->back()->with('success', "ลบคำทำนาย '{$keyword}' เรียบร้อยแล้ว");
    }
}
